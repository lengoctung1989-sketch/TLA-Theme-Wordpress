<?php
/**
 * Mô-đun "Mục lục nội dung" (CP8 — yêu cầu Tùng 2026-09-17).
 *
 * Nút DỌC cố định ở mép màn hình ("Mục lục nội dung") → bấm mở DRAWER danh sách heading của bài;
 * bấm 1 mục thì cuộn tới heading đó, cuộn trang thì mục đang xem được tô nền.
 * Tham khảo layout demo tungleads.com/khoa-hoc/... (đo 2026-09-17):
 *   · nút `.toc-toggle`: fixed, `right: 0; top: 500px` + `translateY(-50%)`, 41×163, nền xanh
 *     `rgb(38,94,207)`, chữ trắng 12.5/700, bo 2 góc TRÁI `11.2px`, chữ XOAY DỌC
 *     (`writing-mode: vertical-rl`), icon danh sách 15×15, `aria-expanded`;
 *   · drawer `.toc-panel`: `fixed; z-index: 60; width: 340px; height: 100vh; background: #fff`,
 *     shadow đổ sang trái; trong đó `.toc-panel-list` cuộn được (`padding: 12px`);
 *   · mục `li.toc-level-2` (H2: đậm, màu đậm) / `li.toc-level-3` (H3: nhạt hơn) — mỗi link có
 *     `<span class="toc-item-number">1</span>` ⇒ ĐÁNH SỐ 1 · 2 · 2.1 · 3…;
 *   · mục ĐANG XEM được tô nền `rgb(234,241,255)` (đo sau khi cuộn: nền từ `rgba(…,.114)` → đặc).
 *
 * Vì sao để ở PLUGIN: đây là dữ liệu/hành vi (quét heading, sinh id, cấu hình) — theme chỉ là skin.
 * CSS/JS đi kèm plugin luôn (`assets/toc.css`, `assets/toc.js`) nhưng **màu lấy từ token của theme**
 * (`var(--cp-accent, …)`) nên tự khớp skin của theme; **Settings → PL Tiện Ích** cho phép đổi màu/kiểu.
 *
 * @package TL\Utilities
 */

defined( 'ABSPATH' ) || exit;

const TL_CP_TOC_OPTION = 'tlcp_toc';

/** Giá trị mặc định (khoá nào chưa lưu thì dùng ở đây). */
function tlcp_toc_defaults(): array {
	return array(
		'on'         => '',                       // '1' = bật.
		'types'      => array( 'post', 'page' ),  // post type áp dụng.
		'min'        => 3,                        // số heading tối thiểu mới hiện mục lục.
		'depth'      => 3,                        // 2 = H2 · 3 = H2+H3 · 4 = H2+H3+H4.
		'number'     => '1',                      // đánh số 1 · 2 · 2.1 (như demo).
		'label'      => 'Mục lục nội dung',       // nhãn nút dọc + tiêu đề khối trong nội dung.
		'side'       => 'right',                   // right | left (nút dọc).
		'color'      => '',                       // rỗng = dùng --cp-accent của theme.
		'spy'        => '1',                      // tô nền mục đang xem (drawer).
		'mobile'     => '1',                      // hiện nút dọc cả trên mobile (≤768px).
		'float'      => '1',                      // '1' = hiện NÚT DỌC + drawer ở mép màn hình.
		'inline'     => '1',                      // '1' = hiện KHỐI MỤC LỤC trong nội dung bài.
		'inline_pos' => 'top',                    // top = đầu bài · p1 = sau đoạn mở đầu.
		'inline_open'=> '1',                      // '1' = mở sẵn khối trong nội dung.
		'exclude'    => '',                       // danh sách ID bài KHÔNG áp dụng (mỗi dòng 1 ID).
	);
}

/** Cấu hình hiện tại (đã trộn mặc định). */
function tlcp_toc(): array {
	$saved = get_option( TL_CP_TOC_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();
	$out   = tlcp_toc_defaults();

	foreach ( $out as $key => $default ) {
		if ( array_key_exists( $key, $saved ) ) {
			$out[ $key ] = $saved[ $key ];
		}
	}

	$out['types'] = array_values( array_filter( array_map( 'strval', (array) $out['types'] ) ) );

	return $out;
}

/** Có bật + có ít nhất 1 post type? */
function tlcp_toc_active(): bool {
	$o = tlcp_toc();

	return ! empty( $o['on'] ) && ! empty( $o['types'] );
}

/** Danh sách post type công khai để tick trong Settings (bỏ attachment). */
function tlcp_toc_post_types(): array {
	$types = get_post_types( array( 'public' => true ), 'objects' );
	$out   = array();

	foreach ( $types as $slug => $obj ) {
		if ( 'attachment' === $slug ) {
			continue;
		}
		$out[ $slug ] = $obj->labels->singular_name . ' (' . $slug . ')';
	}

	return $out;
}

/**
 * Chuẩn hoá dữ liệu form. QUAN TRỌNG: checkbox KHÔNG tick thì trình duyệt KHÔNG gửi khoá đó ⇒
 * phải tự đặt lại '' cho các cờ, không thì bật rồi không tắt được.
 */
function tlcp_toc_sanitize( $value ): array {
	$out   = tlcp_toc_defaults();
	$value = is_array( $value ) ? $value : array();

	$out['on']     = empty( $value['on'] ) ? '' : '1';
	$out['number'] = empty( $value['number'] ) ? '' : '1';
	$out['spy']    = empty( $value['spy'] ) ? '' : '1';
	$out['mobile'] = empty( $value['mobile'] ) ? '' : '1';
	$out['float']  = empty( $value['float'] ) ? '' : '1';
	$out['inline'] = empty( $value['inline'] ) ? '' : '1';
	$out['inline_open'] = empty( $value['inline_open'] ) ? '' : '1';
	$out['inline_pos']  = ( isset( $value['inline_pos'] ) && 'p1' === $value['inline_pos'] ) ? 'p1' : 'top';

	$types = isset( $value['types'] ) ? array_map( 'sanitize_key', (array) $value['types'] ) : array();
	$types = array_values( array_filter( $types, 'post_type_exists' ) );
	$out['types'] = $types ? $types : array();

	$min         = isset( $value['min'] ) ? absint( $value['min'] ) : 3;
	$out['min']  = max( 1, min( 20, $min ) );

	$depth        = isset( $value['depth'] ) ? absint( $value['depth'] ) : 3;
	$out['depth'] = in_array( $depth, array( 2, 3, 4 ), true ) ? $depth : 3;

	$label         = isset( $value['label'] ) ? sanitize_text_field( (string) $value['label'] ) : '';
	$out['label']  = '' !== $label ? $label : tlcp_toc_defaults()['label'];

	$out['side'] = ( isset( $value['side'] ) && 'left' === $value['side'] ) ? 'left' : 'right';

	$color         = isset( $value['color'] ) ? sanitize_hex_color( (string) $value['color'] ) : '';
	$out['color']  = $color ? $color : '';

	if ( isset( $value['exclude'] ) ) {
		$ids           = preg_split( '/[\s,]+/', (string) $value['exclude'] );
		$ids           = array_filter( array_map( 'absint', (array) $ids ) );
		$out['exclude'] = implode( "\n", $ids );
	}

	return $out;
}

/** Danh sách ID bị loại trừ (mảng int). */
function tlcp_toc_excluded_ids(): array {
	$raw = (string) tlcp_toc()['exclude'];
	$ids = preg_split( '/[\s,]+/', $raw );

	return array_filter( array_map( 'absint', (array) $ids ) );
}

/* ============================ ĐĂNG KÝ OPTION ============================ */

add_action(
	'admin_init',
	static function (): void {
		register_setting(
			'tlcp_support',
			TL_CP_TOC_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlcp_toc_sanitize',
				'default'           => array(),
			)
		);
	}
);


/* ============================ FRONT-END ============================ */

/** Có áp mục lục cho request này không? */
function tlcp_toc_should_run(): bool {
	if ( is_admin() || is_feed() || is_embed() ) {
		return false;
	}
	if ( ! tlcp_toc_active() || ! is_singular() ) {
		return false;
	}

	$post = get_post();
	if ( ! $post ) {
		return false;
	}

	$o = tlcp_toc();
	if ( ! in_array( $post->post_type, $o['types'], true ) ) {
		return false;
	}
	if ( in_array( (int) $post->ID, tlcp_toc_excluded_ids(), true ) ) {
		return false;
	}

	return (bool) apply_filters( 'tlcp_toc_should_run', true, $post );
}

/** Nạp CSS/JS khi trang CÓ THỂ có mục lục (đúng post type) — chưa biết số heading nên nạp sớm. */
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( ! tlcp_toc_should_run() ) {
			return;
		}

		// Cả 2 cách hiển thị đều tắt ⇒ không có gì để nạp (đỡ 1 request CSS + 1 request JS).
		$o = tlcp_toc();
		if ( empty( $o['float'] ) && empty( $o['inline'] ) ) {
			return;
		}

		$css = TL_CP_DIR . 'assets/toc.css';
		$js  = TL_CP_DIR . 'assets/toc.js';
		$ver = defined( 'TL_CP_VERSION' ) ? TL_CP_VERSION : '0';

		wp_enqueue_style(
			'tlcp-toc',
			TL_CP_URL . 'assets/toc.css',
			array(),
			file_exists( $css ) ? (string) filemtime( $css ) : $ver
		);
		wp_enqueue_script(
			'tlcp-toc',
			TL_CP_URL . 'assets/toc.js',
			array(),
			file_exists( $js ) ? (string) filemtime( $js ) : $ver,
			true
		);
	},
	30
);

/** Lưu danh sách heading đã quét (theo post ID, in ở `wp_footer`). */
function tlcp_toc_store( int $post_id, array $items ): void {
	$GLOBALS['tlcp_toc_data'][ $post_id ] = $items;
}

/** Lấy danh sách heading của 1 bài. */
function tlcp_toc_data( int $post_id ): array {
	return isset( $GLOBALS['tlcp_toc_data'][ $post_id ] ) ? (array) $GLOBALS['tlcp_toc_data'][ $post_id ] : array();
}

/**
 * Sinh id duy nhất cho heading từ TIÊU ĐỀ (giống demo: "Giới thiệu tổng quan khóa học Google Ads:"
 * → `gioi-thieu-tong-quan-khoa-hoc-google-ads`). `sanitize_title()` có `remove_accents()` nên bỏ
 * được dấu tiếng Việt; trùng nhau thì thêm `-2`, `-3`… (`$used` giữ các id đã dùng trong bài).
 */
function tlcp_toc_id( string $text, array &$used ): string {
	$base = sanitize_title( $text );
	$base = '' !== $base ? $base : 'muc';

	$id = $base;
	$n  = 2;
	while ( isset( $used[ $id ] ) ) {
		$id = $base . '-' . $n;
		$n++;
	}
	$used[ $id ] = true;

	return $id;
}

/**
 * Quét nội dung: thêm `id` cho heading còn thiếu + trả về danh sách mục (kèm số thứ tự).
 *
 * Dùng regex chứ không `WP_HTML_Tag_Processor` vì cần ĐỌC CHỮ trong heading để đặt nhãn/id
 * (processor chỉ đọc được thuộc tính). Heading không lồng nhau nên regex an toàn.
 *
 * `the_content` chạy ở prio 12 ⇒ SAU `do_shortcode` (11) nên heading do shortcode sinh ra cũng được quét.
 */
function tlcp_toc_scan( string $content, int $max_level, bool $number ): array {
	$items = array();
	$used  = array();
	$c1    = 0;
	$c2    = 0;
	$c3    = 0;

	$content = (string) preg_replace_callback(
		'#<h([2-4])(\s[^>]*)?>(.*?)</h\1>#is',
		static function ( $m ) use ( &$items, &$used, &$c1, &$c2, &$c3, $max_level, $number ) {
			$level = (int) $m[1];
			if ( $level > $max_level ) {
				return $m[0];
			}

			$attrs = isset( $m[2] ) ? $m[2] : '';
			$inner = $m[3];
			$label = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $inner, true ) ) );
			if ( '' === $label ) {
				return $m[0];
			}

			$id = '';
			if ( preg_match( '/\sid\s*=\s*(["\'])(.*?)\1/i', $attrs, $mm ) ) {
				$id = trim( $mm[2] );
			}
			if ( '' === $id ) {
				$id     = tlcp_toc_id( $label, $used );
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			} else {
				$used[ $id ] = true;
			}

			if ( 2 === $level ) {
				$c1++;
				$c2 = 0;
				$c3 = 0;
			} elseif ( 3 === $level ) {
				$c2++;
				$c3 = 0;
			} else {
				$c3++;
			}

			$num = '';
			if ( $number ) {
				if ( 2 === $level ) {
					$num = (string) $c1;
				} elseif ( 3 === $level ) {
					$num = $c1 . '.' . $c2;
				} else {
					$num = $c1 . '.' . $c2 . '.' . $c3;
				}
			}

			$items[] = array(
				'level' => $level,
				'id'    => $id,
				'text'  => $label,
				'num'   => $num,
			);

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		$content
	);

	return array(
		'content' => $content,
		'items'   => $items,
	);
}

/**
 * In NÚT DỌC + DRAWER ở `wp_footer` prio **5** — PHẢI chạy TRƯỚC `wp_print_footer_scripts`
 * (core gắn vào `wp_footer` prio **20**): ở prio 20 markup sẽ được in SAU thẻ `<script>` của
 * `assets/toc.js`, lúc đó JS chạy mà chưa có nút nào trong DOM ⇒ không gắn được sự kiện
 * (đo 2026-09-17: bấm nút không mở, `aria-expanded` đứng nguyên `false`).
 *
 * KHÔNG in trong nội dung: phần tử `position: fixed` nằm trong nội dung bài có thể bị ancestor
 * `transform`/`filter` biến thành containing block ⇒ neo sai. Ở footer thì neo đúng viewport,
 * vẫn là con của `<body>` nên không phá layout.
 */
add_action(
	'wp_footer',
	static function (): void {
		$o = tlcp_toc();

		// Tuỳ chọn “Hiện nút dọc + drawer” tắt ⇒ chỉ còn khối mục lục trong nội dung (nếu bật).
		if ( empty( $o['float'] ) ) {
			return;
		}

		$post_id = (int) get_queried_object_id();
		$items   = tlcp_toc_data( $post_id );

		if ( ! $items ) {
			return;
		}

		$classes = array( 'tlcp-toc', 'tlcp-toc--' . $o['side'] );

		if ( empty( $o['mobile'] ) ) {
			$classes[] = 'tlcp-toc--no-mobile';
		}
		if ( empty( $o['spy'] ) ) {
			$classes[] = 'tlcp-toc--no-spy';
		}

		$label = (string) $o['label'];
		$style = '' !== (string) $o['color'] ? ' style="--tlcp-toc-color:' . esc_attr( (string) $o['color'] ) . '"' : '';

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $style . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- chuỗi đã escape từng phần.
		?>
		<button type="button" class="tlcp-toc__toggle" aria-expanded="false" aria-controls="tlcp-toc-panel">
			<span class="tlcp-toc__toggle-ic" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h.01"/><path d="M3 12h.01"/><path d="M3 19h.01"/><path d="M8 5h13"/><path d="M8 12h13"/><path d="M8 19h13"/></svg>
			</span>
			<span class="tlcp-toc__toggle-txt"><?php echo esc_html( $label ); ?></span>
		</button>

		<nav id="tlcp-toc-panel" class="tlcp-toc__panel" aria-label="<?php echo esc_attr( $label ); ?>" inert>
			<div class="tlcp-toc__head">
				<span class="tlcp-toc__title"><?php echo esc_html( $label ); ?></span>
				<button type="button" class="tlcp-toc__close" aria-label="<?php esc_attr_e( 'Đóng mục lục', 'tl-site-caophat' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
				</button>
			</div>
			<ol class="tlcp-toc__list">
				<?php foreach ( $items as $tlcp_item ) : ?>
					<li class="tlcp-toc__item tlcp-toc__item--lvl<?php echo (int) $tlcp_item['level']; ?>">
						<a class="tlcp-toc__link" href="#<?php echo esc_attr( (string) $tlcp_item['id'] ); ?>">
							<?php if ( '' !== (string) $tlcp_item['num'] ) : ?>
								<span class="tlcp-toc__num"><?php echo esc_html( (string) $tlcp_item['num'] ); ?></span>
							<?php endif; ?>
							<span class="tlcp-toc__txt"><?php echo esc_html( (string) $tlcp_item['text'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>
		<?php
		echo '</div>';
	},
	5
);

/* ============================ KHỐI MỤC LỤC TRONG NỘI DUNG ============================ */

/**
 * HTML khối mục lục INLINE (tuỳ chọn “Hiện khối mục lục trong nội dung bài viết”).
 *
 * Dùng `<details>`/`<summary>` HTML gốc để thu gọn/mở **KHÔNG CẦN JS** (`open` = mở sẵn): tắt JS thì
 * khối vẫn bấm mở/thu bình thường; JS chỉ thêm phần cuộn mượt + bù header sticky cho các link.
 */
function tlcp_toc_inline_html( array $items, array $o ): string {
	// Màu chọn ở Settings phải truyền vào khối này bằng biến CSS — khối nằm trong nội dung, KHÔNG nằm
	// trong wrapper `.tlcp-toc` (nơi biến đó được gắn) ⇒ thiếu dòng này khối sẽ lệch màu với nút dọc.
	$style = '' !== (string) $o['color'] ? ' style="--tlcp-toc-color:' . esc_attr( (string) $o['color'] ) . '"' : '';

	$html  = '<details class="tlcp-toc-inline"' . $style . ( empty( $o['inline_open'] ) ? '' : ' open' ) . '>';
	$html .= '<summary class="tlcp-toc-inline__sum"><span class="tlcp-toc-inline__title">' . esc_html( (string) $o['label'] ) . '</span></summary>';
	$html .= '<ol class="tlcp-toc-inline__list">';

	foreach ( $items as $item ) {
		$level = (int) $item['level'];
		$num   = (string) $item['num'];

		$html .= '<li class="tlcp-toc-inline__item tlcp-toc-inline__item--lvl' . $level . '">';
		$html .= '<a class="tlcp-toc-inline__link" href="#' . esc_attr( (string) $item['id'] ) . '">';

		if ( '' !== $num ) {
			$html .= '<span class="tlcp-toc-inline__num">' . esc_html( $num ) . '</span>';
		}

		$html .= '<span class="tlcp-toc-inline__txt">' . esc_html( (string) $item['text'] ) . '</span>';
		$html .= '</a></li>';
	}

	$html .= '</ol></details>';

	return $html;
}

/**
 * Chèn khối mục lục vào nội dung: `top` = ngay đầu bài · `p1` = sau ĐOẠN MỞ ĐẦU (thẻ `</p>` đầu tiên —
 * để khối không chen giữa tiêu đề và đoạn dẫn). Bài không có `</p>` (chỉ ảnh/bảng) thì rơi về đầu bài.
 */
function tlcp_toc_inline_insert( string $content, string $box, string $pos ): string {
	if ( '' === $box ) {
		return $content;
	}

	if ( 'p1' === $pos && preg_match( '#</p>#i', $content, $m, PREG_OFFSET_CAPTURE ) ) {
		$at = (int) $m[0][1] + strlen( (string) $m[0][0] );

		return substr( $content, 0, $at ) . $box . substr( $content, $at );
	}

	return $box . $content;
}

/**
 * Filter `the_content` (prio 12 — chạy SAU `do_shortcode` prio 11): thêm `id` cho heading còn thiếu
 * và lưu danh sách mục để in nút/drawer ở footer. Chỉ lưu khi đủ số heading tối thiểu; dữ liệu gắn
 * theo post ID nên không lẫn khi theme gọi `the_content()` cho bài khác.
 */
add_filter(
	'the_content',
	static function ( $content ) {
		if ( ! is_string( $content ) || '' === $content || ! tlcp_toc_should_run() ) {
			return $content;
		}

		$o     = tlcp_toc();
		$scan  = tlcp_toc_scan( $content, (int) $o['depth'], ! empty( $o['number'] ) );
		$items = $scan['items'];

		if ( count( $items ) < max( 1, (int) $o['min'] ) ) {
			return $content;
		}

		$post_id = (int) get_the_ID();
		if ( $post_id > 0 ) {
			tlcp_toc_store( $post_id, $items );
		}

		$content = $scan['content'];

		// Tuỳ chọn “Hiện khối mục lục trong nội dung bài viết” ⇒ chèn NGAY vào nội dung (sau khi quét
		// heading, nên khối không tự lọt vào danh sách mục).
		if ( ! empty( $o['inline'] ) ) {
			$content = tlcp_toc_inline_insert( $content, tlcp_toc_inline_html( $items, $o ), (string) $o['inline_pos'] );
		}

		return $content;
	},
	12
);

/* ============================ GIAO DIỆN SETTINGS ============================ */

/** Mục "Mục lục nội dung" trong Settings → PL Tiện Ích (gọi từ `tlcp_support_page()`). */
function tlcp_toc_settings_ui(): void {
	$o     = tlcp_toc();
	$types = tlcp_toc_post_types();
	?>
	<hr style="margin:28px 0 0;">
	<h2><?php esc_html_e( 'Mục lục nội dung', 'tl-site-caophat' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Nút dọc cố định ở mép màn hình mở ra danh sách heading của bài; bấm 1 mục thì nhảy tới, cuộn trang thì mục đang xem được tô nền.', 'tl-site-caophat' ); ?><br>
		<?php esc_html_e( 'Chỉ hiện khi bài có ĐỦ số heading tối thiểu bên dưới — bài ngắn không bị làm phiền.', 'tl-site-caophat' ); ?>
	</p>

	<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin:10px 0 0;">
		<input type="checkbox" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[on]' ); ?>" value="1" <?php checked( ! empty( $o['on'] ) ); ?>>
		<?php esc_html_e( 'Bật mục lục nội dung', 'tl-site-caophat' ); ?>
	</label>

	<h3 style="margin:20px 0 4px;"><?php esc_html_e( 'Áp dụng cho', 'tl-site-caophat' ); ?></h3>
	<fieldset style="margin:0;">
		<?php foreach ( $types as $tlcp_slug => $tlcp_name ) : ?>
			<label style="display:inline-flex;align-items:center;gap:6px;margin:0 16px 6px 0;">
				<input type="checkbox" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[types][]' ); ?>" value="<?php echo esc_attr( $tlcp_slug ); ?>" <?php checked( in_array( $tlcp_slug, $o['types'], true ) ); ?>>
				<?php echo esc_html( $tlcp_name ); ?>
			</label>
		<?php endforeach; ?>
	</fieldset>

	<h3 style="margin:20px 0 4px;"><?php esc_html_e( 'Số cấp heading', 'tl-site-caophat' ); ?></h3>
	<select name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[depth]' ); ?>">
		<option value="2" <?php selected( 2, (int) $o['depth'] ); ?>><?php esc_html_e( 'Chỉ H2', 'tl-site-caophat' ); ?></option>
		<option value="3" <?php selected( 3, (int) $o['depth'] ); ?>><?php esc_html_e( 'H2 + H3 (khuyên dùng)', 'tl-site-caophat' ); ?></option>
		<option value="4" <?php selected( 4, (int) $o['depth'] ); ?>><?php esc_html_e( 'H2 + H3 + H4', 'tl-site-caophat' ); ?></option>
	</select>
	<p style="margin:14px 0 0;">
		<label style="display:inline-flex;align-items:center;gap:8px;font-weight:600;">
			<?php esc_html_e( 'Số heading tối thiểu', 'tl-site-caophat' ); ?>
			<input type="number" min="1" max="20" step="1" style="width:80px;" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[min]' ); ?>" value="<?php echo esc_attr( (string) $o['min'] ); ?>">
		</label>
	</p>

	<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin:12px 0 0;">
		<input type="checkbox" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[number]' ); ?>" value="1" <?php checked( ! empty( $o['number'] ) ); ?>>
		<?php esc_html_e( 'Đánh số mục (1 · 2 · 2.1 · 3…)', 'tl-site-caophat' ); ?>
	</label>

	<h3 style="margin:22px 0 4px;"><?php esc_html_e( 'Cách hiển thị', 'tl-site-caophat' ); ?></h3>
	<label style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
		<input type="checkbox" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[float]' ); ?>" value="1" <?php checked( ! empty( $o['float'] ) ); ?>>
		<?php esc_html_e( 'Hiện NÚT DỌC + drawer ở mép màn hình (kiểu demo tungleads.com)', 'tl-site-caophat' ); ?>
	</label>
	<label style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
		<input type="checkbox" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[inline]' ); ?>" value="1" <?php checked( ! empty( $o['inline'] ) ); ?>>
		<?php esc_html_e( 'Hiện KHỐI MỤC LỤC trong nội dung bài viết', 'tl-site-caophat' ); ?>
	</label>
	<p style="margin:0 0 6px 26px;">
		<label style="display:inline-flex;align-items:center;gap:8px;">
			<?php esc_html_e( 'Vị trí khối trong nội dung', 'tl-site-caophat' ); ?>
			<select name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[inline_pos]' ); ?>">
				<option value="top" <?php selected( 'top', $o['inline_pos'] ); ?>><?php esc_html_e( 'Đầu bài viết', 'tl-site-caophat' ); ?></option>
				<option value="p1" <?php selected( 'p1', $o['inline_pos'] ); ?>><?php esc_html_e( 'Sau đoạn mở đầu', 'tl-site-caophat' ); ?></option>
			</select>
		</label>
	</p>
	<label style="display:flex;align-items:center;gap:8px;margin:0 0 0 26px;">
		<input type="checkbox" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[inline_open]' ); ?>" value="1" <?php checked( ! empty( $o['inline_open'] ) ); ?>>
		<?php esc_html_e( 'Mở sẵn khối trong nội dung (bỏ tick = thu gọn, bấm tiêu đề mới mở)', 'tl-site-caophat' ); ?>
	</label>
	<p class="description">
		<?php esc_html_e( 'Tick cả 2 = vừa có nút dọc vừa có khối trong bài; chỉ tick 1 cũng chạy bình thường. Khối trong nội dung dùng thẻ <details> nên bấm mở/thu được cả khi trình duyệt chặn JS.', 'tl-site-caophat' ); ?>
	</p>

	<h3 style="margin:20px 0 4px;"><?php esc_html_e( 'Nút dọc ở mép màn hình', 'tl-site-caophat' ); ?></h3>
	<p style="margin:0 0 6px;">
		<label style="display:inline-flex;align-items:center;gap:8px;">
			<?php esc_html_e( 'Nhãn nút / tiêu đề khối', 'tl-site-caophat' ); ?>
			<input type="text" class="regular-text" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[label]' ); ?>" value="<?php echo esc_attr( (string) $o['label'] ); ?>">
		</label>
	</p>
	<p style="margin:0 0 6px;">
		<label style="display:inline-flex;align-items:center;gap:8px;">
			<?php esc_html_e( 'Mép màn hình', 'tl-site-caophat' ); ?>
			<select name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[side]' ); ?>">
				<option value="right" <?php selected( 'right', $o['side'] ); ?>><?php esc_html_e( 'Bên phải', 'tl-site-caophat' ); ?></option>
				<option value="left" <?php selected( 'left', $o['side'] ); ?>><?php esc_html_e( 'Bên trái', 'tl-site-caophat' ); ?></option>
			</select>
		</label>
		<label style="display:inline-flex;align-items:center;gap:8px;margin-left:16px;">
			<?php esc_html_e( 'Màu nút', 'tl-site-caophat' ); ?>
			<input type="color" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[color]' ); ?>" value="<?php echo esc_attr( '' !== (string) $o['color'] ? (string) $o['color'] : '#c8471f' ); ?>">
			<span class="description"><?php esc_html_e( 'Để nguyên = dùng màu nhấn của theme.', 'tl-site-caophat' ); ?></span>
		</label>
	</p>
	<label style="display:inline-flex;align-items:center;gap:8px;margin:0 16px 8px 0;">
		<input type="checkbox" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[mobile]' ); ?>" value="1" <?php checked( ! empty( $o['mobile'] ) ); ?>>
		<?php esc_html_e( 'Hiện nút dọc trên mobile (≤768px)', 'tl-site-caophat' ); ?>
	</label>
	<label style="display:inline-flex;align-items:center;gap:8px;margin-bottom:8px;">
		<input type="checkbox" name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[spy]' ); ?>" value="1" <?php checked( ! empty( $o['spy'] ) ); ?>>
		<?php esc_html_e( 'Tô nền mục đang xem trong drawer', 'tl-site-caophat' ); ?>
	</label>

	<h3 style="margin:20px 0 4px;"><?php esc_html_e( 'Loại trừ', 'tl-site-caophat' ); ?></h3>
	<textarea name="<?php echo esc_attr( TL_CP_TOC_OPTION . '[exclude]' ); ?>" rows="4" class="large-text code" placeholder="<?php esc_attr_e( 'ID bài KHÔNG hiện mục lục — mỗi dòng 1 ID', 'tl-site-caophat' ); ?>"><?php echo esc_textarea( (string) $o['exclude'] ); ?></textarea>
	<p class="description"><?php esc_html_e( 'Ví dụ: 1234 rồi xuống dòng 5678. Dùng khi có bài dài nhưng không muốn hiện mục lục.', 'tl-site-caophat' ); ?></p>
	<?php
}

