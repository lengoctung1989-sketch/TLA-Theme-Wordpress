<?php
/**
 * Plugin Name:  TL Site — Cao Phát
 * Description:  Tầng dữ liệu / hành vi riêng của caophat.vn (tracking, sau này: CPT, taxonomy, form). Tách khỏi theme để đổi giao diện không mất data.
 * Version:      0.4.0
 * Requires PHP: 8.2
 * Author:       Tung Le Ads
 * Author URI:   https://tungleads.com/
 *
 * @package TL\Site\CaoPhat
 */

defined( 'ABSPATH' ) || exit;

/** Đường dẫn/URL plugin + version lấy từ header (dùng cho cache-busting asset). */
define( 'TL_CP_FILE', __FILE__ );
define( 'TL_CP_DIR', plugin_dir_path( __FILE__ ) );
define( 'TL_CP_URL', plugin_dir_url( __FILE__ ) );
define( 'TL_CP_VERSION', '0.4.0' );

/** CP8 — Mục lục nội dung (nút dọc + drawer): cấu hình ở Settings → Cao Phát. */
require_once __DIR__ . '/includes/toc.php';

/*
 * ---------------------------------------------------------------------------
 * Tracking — chuyển từ Flatsome → Advanced → Global HTML sang đây.
 * ID marketing là định danh công khai (đã lộ trong HTML trang), không phải secret.
 * XÁC MINH lại 4 ID dưới trước khi bật, rồi GỠ đoạn tương ứng khỏi Flatsome
 * Global HTML CÙNG LÚC — không để cả hai cùng chạy (double-count).
 * Ghi chú: GTM (KCVHR8P) thường đã chứa GA + Google Ads + Meta Pixel. Nếu đúng vậy
 * thì bỏ 3 khối gtag/pixel bên dưới, chỉ giữ GTM. Giữ nguyên 4 khối = sao y production.
 * ---------------------------------------------------------------------------
 */
const TL_CP_GTM_ID   = 'GTM-KCVHR8P';
const TL_CP_GA4_ID    = 'G-L37N4Q06LP';
const TL_CP_GADS_ID   = 'AW-10871632223';
const TL_CP_PIXEL_ID  = '5267684856622253';

/** Scripts trong <head>. */
add_action(
	'wp_head',
	static function (): void {
		?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( TL_CP_GTM_ID ); ?>');</script>
<!-- gtag (GA4 + Google Ads) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( TL_CP_GA4_ID ); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo esc_js( TL_CP_GA4_ID ); ?>');
gtag('config', '<?php echo esc_js( TL_CP_GADS_ID ); ?>');
</script>
<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo esc_js( TL_CP_PIXEL_ID ); ?>');
fbq('track', 'PageView');
</script>
		<?php
	},
	1
);

/** Fallback <noscript> ngay sau <body>. */
add_action(
	'wp_body_open',
	static function (): void {
		?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( TL_CP_GTM_ID ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( TL_CP_PIXEL_ID ); ?>&ev=PageView&noscript=1" alt=""/></noscript>
		<?php
	}
);

/*
 * ---------------------------------------------------------------------------
 * CHÈN MÃ TRACKING TÙY Ý — 3 VỊ TRÍ (Tùng yêu cầu 2026-09-16).
 * Sửa ở Settings → Cao Phát, dán NGUYÊN mã nhà cung cấp cấp (kèm cả thẻ <script> nếu có):
 *   head   → ngay sau thẻ <head>      (hook `wp_head` prio 1 — sớm nhất có thể)
 *   body   → ngay sau thẻ mở <body>   (hook `wp_body_open` prio 1 — theme con + theme cha đều gọi)
 *   footer → cuối trang, trước </body> (hook `wp_footer` prio 99 — sau mọi script khác)
 * ⚠️ Dán mã TRÙNG với 4 khối tracking sẵn có ở trên (GTM/GA4/Google Ads/Meta Pixel) thì số liệu
 *    sẽ BỊ ĐẾM ĐÔI ⇒ gỡ một trong hai chỗ (đúng cảnh báo ở đầu file).
 * ---------------------------------------------------------------------------
 */
const TL_CP_TRACKING_OPTION = 'tlcp_tracking_code';

/**
 * Mã tracking đang lưu, luôn đủ 3 khoá `head` / `body` / `footer` (chuỗi, đã trim).
 *
 * @return array{head:string,body:string,footer:string}
 */
function tlcp_tracking_code(): array {
	$saved = get_option( TL_CP_TRACKING_OPTION, array() );
	$out   = array(
		'head'   => '',
		'body'   => '',
		'footer' => '',
	);

	if ( is_array( $saved ) ) {
		foreach ( $out as $key => $unused ) {
			if ( isset( $saved[ $key ] ) && is_string( $saved[ $key ] ) ) {
				$out[ $key ] = trim( $saved[ $key ] );
			}
		}
	}

	return (array) apply_filters( 'tlcp_tracking_code', $out );
}

/**
 * Sanitize mã tracking khi lưu.
 *
 * Mã tracking LÀ code nên KHÔNG được lọc theo kiểu văn bản (lọc là hỏng mã), nhưng chỉ giữ nguyên
 * văn khi người lưu có quyền `unfiltered_html`. Thiếu quyền đó (VD quản trị viên trên multisite)
 * thì cho qua `wp_kses_post` ⇒ thẻ `<script>` bị bỏ, an toàn hơn là cho chèn JS tuỳ ý.
 *
 * @param mixed $value Giá trị từ form.
 * @return array{head:string,body:string,footer:string}
 */
function tlcp_sanitize_tracking_code( $value ): array {
	$out     = array(
		'head'   => '',
		'body'   => '',
		'footer' => '',
	);
	$can_raw = current_user_can( 'unfiltered_html' );

	if ( ! is_array( $value ) ) {
		return $out;
	}

	foreach ( $out as $key => $unused ) {
		$raw = isset( $value[ $key ] ) && is_string( $value[ $key ] ) ? trim( $value[ $key ] ) : '';
		if ( '' === $raw ) {
			continue;
		}
		$out[ $key ] = $can_raw ? $raw : wp_kses_post( $raw );
	}

	return $out;
}

/**
 * In mã của 1 vị trí (không in gì khi ô trống).
 *
 * @param string $position `head` | `body` | `footer`.
 */
function tlcp_print_tracking_code( string $position ): void {
	if ( is_admin() ) {
		return; // Không in trong wp-admin.
	}

	$code = tlcp_tracking_code();
	if ( ! isset( $code[ $position ] ) || '' === $code[ $position ] ) {
		return;
	}

	echo "\n" . $code[ $position ] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- mã do QUẢN TRỊ dán, in nguyên văn mới chạy được.
}

add_action( 'wp_head', static fn() => tlcp_print_tracking_code( 'head' ), 1 );
add_action( 'wp_body_open', static fn() => tlcp_print_tracking_code( 'body' ), 1 );
add_action( 'wp_footer', static fn() => tlcp_print_tracking_code( 'footer' ), 99 );

/*
 * ---------------------------------------------------------------------------
 * CHẾ ĐỘ BẢO TRÌ (Tùng yêu cầu 2026-09-17) — bật/tắt ở Settings → Cao Phát.
 *   • Khách CHƯA đăng nhập → thấy trang thông báo (nội dung tự nhập).
 *   • Người có quyền `manage_options` (lọc được qua `tlcp_maintenance_capability`)
 *     vẫn xem web BÌNH THƯỜNG ⇒ bật bảo trì rồi vẫn sửa nội dung được.
 *   • Trả **HTTP 503 + `Retry-After`** = đúng chuẩn cho bảo trì TẠM THỜI
 *     (Google giữ trang trong index, không đánh rớt) + `noindex` cho an toàn.
 *   • Chặn cache (`DONOTCACHEPAGE` — LiteSpeed đọc biến này) để không cache trang bảo trì.
 *   • BỎ QUA: `wp-admin`, AJAX, cron, WP-CLI, REST/JSON — nếu không sẽ làm hỏng trình
 *     soạn thảo (Gutenberg gọi `/wp-json/`) và các tác vụ nền.
 *   • Trang bảo trì là HTML + CSS nội tuyến, KHÔNG dùng CSS/JS của theme ⇒ vẫn hiện
 *     đúng kể cả khi theme đang lỗi hoặc đang nâng cấp.
 * ---------------------------------------------------------------------------
 */
const TL_CP_MAINTENANCE_OPTION = 'tlcp_maintenance';

/**
 * Trạng thái bảo trì hiện tại.
 *
 * @return array{on:bool,message:string}
 */
function tlcp_maintenance(): array {
	$saved = get_option( TL_CP_MAINTENANCE_OPTION, array() );
	$out   = array(
		'on'      => false,
		'message' => '',
	);

	if ( is_array( $saved ) ) {
		$out['on']      = ! empty( $saved['on'] );
		$out['message'] = isset( $saved['message'] ) && is_string( $saved['message'] ) ? trim( $saved['message'] ) : '';
	}

	return (array) apply_filters( 'tlcp_maintenance', $out );
}

/**
 * Sanitize trạng thái bảo trì: `on` = cờ bật/tắt, `message` = HTML cơ bản (không cho script).
 *
 * @param mixed $value Giá trị từ form.
 * @return array{on:bool,message:string}
 */
function tlcp_sanitize_maintenance( $value ): array {
	$out = array(
		'on'      => false,
		'message' => '',
	);

	if ( ! is_array( $value ) ) {
		return $out;
	}

	$out['on']      = ! empty( $value['on'] );
	$out['message'] = isset( $value['message'] ) && is_string( $value['message'] ) ? wp_kses_post( trim( $value['message'] ) ) : '';

	return $out;
}

/**
 * Người đang xem có được BỎ QUA trang bảo trì không? (mặc định: quản trị viên)
 */
function tlcp_maintenance_bypass(): bool {
	$cap = (string) apply_filters( 'tlcp_maintenance_capability', 'manage_options' );

	return is_user_logged_in() && current_user_can( $cap );
}

/** Trang bảo trì độc lập (HTML + CSS nội tuyến). */
add_action(
	'template_redirect',
	static function (): void {
		$mt = tlcp_maintenance();
		if ( empty( $mt['on'] ) ) {
			return;
		}
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			return;
		}
		if ( tlcp_maintenance_bypass() ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		status_header( 503 );
		header( 'Retry-After: 3600' );
		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );

		$site = (string) get_bloginfo( 'name' );
		$msg  = '' !== $mt['message']
			? $mt['message']
			: '<p>' . esc_html__( 'Website đang được bảo trì để nâng cấp. Vui lòng quay lại sau ít phút.', 'tl-site-caophat' ) . '</p>';
		?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( sprintf( /* translators: %s: tên site. */ __( 'Bảo trì — %s', 'tl-site-caophat' ), $site ) ); ?></title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
	background:#f4f5f7;color:#26221e;
	font:16px/1.65 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}
.box{width:100%;max-width:620px;background:#fff;border-radius:16px;padding:40px 36px;text-align:center;
	box-shadow:0 10px 40px rgba(0,0,0,.08)}
.ic{width:64px;height:64px;margin:0 auto 18px;border-radius:50%;display:grid;place-items:center;
	background:#fdf1e3;color:#c8471f}
.ic svg{width:32px;height:32px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
h1{margin:0 0 6px;font-size:22px;line-height:1.3}
.msg{margin:14px 0 0;font-size:16.5px}
.msg p{margin:0 0 10px}
.msg p:last-child{margin-bottom:0}
.tam{margin:18px 0 0;font-size:14px;color:#6f6a63}
.adm{margin:22px 0 0;font-size:13px}
.adm a{color:#c8471f}
@media(max-width:480px){.box{padding:28px 20px}h1{font-size:19px}}
</style>
</head>
<body>
	<div class="box">
		<div class="ic" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 0 5 5l-9 9a2.8 2.8 0 0 1-4-4z"/><path d="M14.7 6.3 17.6 3.4a4 4 0 0 1 3 4.9"/></svg></div>
		<h1><?php echo esc_html( $site ); ?></h1>
		<div class="msg"><?php echo $msg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- đã lọc bằng wp_kses_post khi lưu. ?></div>
		<p class="tam"><?php esc_html_e( 'Chúng tôi sẽ quay lại sớm nhất có thể — cảm ơn anh/chị đã chờ.', 'tl-site-caophat' ); ?></p>
		<p class="adm"><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Quản trị viên đăng nhập', 'tl-site-caophat' ); ?></a></p>
	</div>
</body>
</html>
		<?php
		exit;
	},
	1
);



/*
 * ---------------------------------------------------------------------------
 * CPT / taxonomy / form site-specific — thêm vào đây khi cần, KHÔNG cho vào theme.
 * Nhớ flush rewrite rules khi kích hoạt plugin nếu có đăng ký rewrite.
 *
 * Ví dụ:
 * add_action( 'init', static function (): void {
 *     register_post_type( 'du_an', array( ... ) );
 * } );
 * ---------------------------------------------------------------------------
 */

/*
 * ---------------------------------------------------------------------------
 * Thông số kỹ thuật sản phẩm — tab riêng trong hộp "Dữ liệu sản phẩm" (admin).
 * Lưu vào post meta `_tlcp_spec_*`. Hiển thị ngoài frontend: CHƯA làm.
 * ---------------------------------------------------------------------------
 */

/** Trường thông số: key => [nhãn, kiểu]. */
function tlcp_spec_fields(): array {
	return array(
		'size'      => array( __( 'Kích thước', 'tl-site-caophat' ), 'text' ),
		'door_type' => array( __( 'Loại cửa', 'tl-site-caophat' ), 'text' ),
		'leaf'      => array( __( 'Cánh', 'tl-site-caophat' ), 'text' ),
		'frame'     => array( __( 'Khung', 'tl-site-caophat' ), 'text' ),
		'features'  => array( __( 'Tính năng', 'tl-site-caophat' ), 'text' ),
		'origin'    => array( __( 'Xuất xứ', 'tl-site-caophat' ), 'text' ),
		'warranty'  => array( __( 'Thời gian bảo hành', 'tl-site-caophat' ), 'text' ),
		'note'      => array( __( 'Ghi chú', 'tl-site-caophat' ), 'textarea' ),
	);
}

/** Thêm tab "Thông số kỹ thuật" vào Product Data. */
add_filter(
	'woocommerce_product_data_tabs',
	static function ( array $tabs ): array {
		$tabs['tlcp_spec'] = array(
			'label'    => __( 'Thông số kỹ thuật', 'tl-site-caophat' ),
			'target'   => 'tlcp_spec_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 65,
		);
		return $tabs;
	}
);

/** Nội dung tab. */
add_action(
	'woocommerce_product_data_panels',
	static function (): void {
		echo '<div id="tlcp_spec_data" class="panel woocommerce_options_panel">';
		foreach ( tlcp_spec_fields() as $key => $def ) {
			list( $label, $type ) = $def;
			$args = array(
				'id'    => "_tlcp_spec_{$key}",
				'label' => $label,
			);
			if ( 'textarea' === $type ) {
				woocommerce_wp_textarea_input( $args );
			} else {
				woocommerce_wp_text_input( $args );
			}
		}
		echo '</div>';
	}
);

/** Lưu (WooCommerce đã verify nonce của nó trước hook này). */
add_action(
	'woocommerce_admin_process_product_object',
	static function ( WC_Product $product ): void {
		foreach ( array_keys( tlcp_spec_fields() ) as $key ) {
			$field = "_tlcp_spec_{$key}";
			$val   = isset( $_POST[ $field ] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
				? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
				: '';
			$product->update_meta_data( $field, $val );
		}
	}
);

/*
 * ---------------------------------------------------------------------------
 * Hotline chi nhánh — dữ liệu dùng chung cho theme (box "Hỗ trợ trực tuyến").
 * Sửa ở Settings → Cao Phát. Lưu option dạng chuỗi, mỗi dòng: "Tên | Số".
 * Theme đọc qua `tlcp_support_branches()`; option trống → dùng danh sách mặc định.
 * ---------------------------------------------------------------------------
 */

const TL_CP_BRANCHES_OPTION = 'tlcp_support_branches';

/** Danh sách mặc định khi admin chưa nhập gì. */
function tlcp_support_branches_default(): array {
	return array(
		array(
			'name' => 'CN Quận 7',
			'tel'  => '0834.484.484',
		),
		array(
			'name' => 'CN Bình Tân',
			'tel'  => '0834.713.713',
		),
		array(
			'name' => 'CN Bến Cát',
			'tel'  => '0814.627.610',
		),
		array(
			'name' => 'Giải đáp thắc mắc',
			'tel'  => '0834.627.627',
		),
	);
}

/**
 * Option → mảng ['name','tel'] để theme in ra.
 * Dòng trống hoặc thiếu dấu "|" bị bỏ qua; không còn dòng hợp lệ nào → mặc định.
 */
function tlcp_support_branches(): array {
	$raw = (string) get_option( TL_CP_BRANCHES_OPTION, '' );
	if ( '' === trim( $raw ) ) {
		return tlcp_support_branches_default();
	}

	$out = array();
	foreach ( preg_split( '/\R/u', $raw ) as $line ) {
		if ( '' === trim( $line ) ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' === $parts[0] || empty( $parts[1] ) ) {
			continue;
		}
		$out[] = array(
			'name' => $parts[0],
			'tel'  => $parts[1],
		);
	}

	return $out ? $out : tlcp_support_branches_default();
}

/** Chuỗi hiển thị trong textarea admin: option thật, chưa có thì hiện mặc định. */
function tlcp_support_branches_text(): string {
	$raw = (string) get_option( TL_CP_BRANCHES_OPTION, '' );
	if ( '' !== trim( $raw ) ) {
		return $raw;
	}

	$lines = array();
	foreach ( tlcp_support_branches_default() as $branch ) {
		$lines[] = $branch['name'] . ' | ' . $branch['tel'];
	}
	return implode( "\n", $lines );
}

/** Menu admin: Settings → Cao Phát. */
add_action(
	'admin_menu',
	static function (): void {
		add_options_page(
			__( 'Cao Phát', 'tl-site-caophat' ),
			__( 'Cao Phát', 'tl-site-caophat' ),
			'manage_options',
			'tlcp-support',
			'tlcp_support_page'
		);
	}
);

/** Đăng ký option (nonce + quyền do options.php lo). */
add_action(
	'admin_init',
	static function (): void {
		register_setting(
			'tlcp_support',
			TL_CP_BRANCHES_OPTION,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'default'           => '',
			)
		);

		// Chèn mã tracking 3 vị trí (mục "Chèn mã tracking" ở trên) — mảng 3 khoá, sanitize riêng.
		register_setting(
			'tlcp_support',
			TL_CP_TRACKING_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlcp_sanitize_tracking_code',
				'default'           => array(
					'head'   => '',
					'body'   => '',
					'footer' => '',
				),
			)
		);

		// Chế độ bảo trì (mục "Chế độ bảo trì" ở trên) — cờ bật/tắt + nội dung thông báo.
		register_setting(
			'tlcp_support',
			TL_CP_MAINTENANCE_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlcp_sanitize_maintenance',
				'default'           => array(
					'on'      => false,
					'message' => '',
				),
			)
		);
	}
);

/**
 * Dòng ghi công dùng chung cho **các plugin của theme** (Tùng yêu cầu 2026-09-16):
 * `Phiên bản <x.y.z> | Bởi <a>Tung Le Ads</a>` — version lấy ĐỘNG từ header plugin nên không lệch
 * khi bump version. Hiện ở cuối trang Settings của mỗi plugin.
 */
function tlcp_credit_line(): string {
	$data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
	$ver  = isset( $data['Version'] ) && '' !== $data['Version'] ? (string) $data['Version'] : '';

	return sprintf(
		/* translators: %s: số phiên bản của plugin. */
		esc_html__( 'Phiên bản %s', 'tl-site-caophat' ),
		esc_html( $ver )
	) . ' | ' . sprintf(
		/* translators: %s: tên tác giả (có link website). */
		esc_html__( 'Bởi %s', 'tl-site-caophat' ),
		'<a href="https://tungleads.com/" target="_blank" rel="noopener">Tung Le Ads</a>'
	);
}

/** Giao diện trang cài đặt. */
function tlcp_support_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Cao Phát', 'tl-site-caophat' ); ?></h1>
		<form action="options.php" method="post">
			<?php settings_fields( 'tlcp_support' ); ?>
			<h2><?php esc_html_e( 'Hotline chi nhánh', 'tl-site-caophat' ); ?></h2>
			<p><?php esc_html_e( 'Mỗi dòng một chi nhánh, dạng: Tên | Số điện thoại', 'tl-site-caophat' ); ?></p>
			<textarea name="<?php echo esc_attr( TL_CP_BRANCHES_OPTION ); ?>" rows="8" class="large-text code"><?php echo esc_textarea( tlcp_support_branches_text() ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Ví dụ: CN Quận 7 | 0834.484.484', 'tl-site-caophat' ); ?><br>
				<?php esc_html_e( 'Thứ tự dòng = thứ tự hiển thị. Xoá trắng rồi lưu = quay về danh sách mặc định.', 'tl-site-caophat' ); ?>
			</p>

			<hr style="margin:28px 0 0;">
			<h2><?php esc_html_e( 'Chèn mã tracking', 'tl-site-caophat' ); ?></h2>
			<p>
				<?php esc_html_e( 'Dán NGUYÊN mã nhà cung cấp cấp (kèm cả thẻ script nếu có) — Google Tag Manager, GA4, Meta Pixel, mã xác minh site, chat widget… Ô trống = không chèn gì.', 'tl-site-caophat' ); ?>
			</p>
			<?php
			$tlcp_code = tlcp_tracking_code();
			$tlcp_pos  = array(
				'head'   => array(
					__( 'Sau thẻ <head>', 'tl-site-caophat' ),
					__( 'Chạy sớm nhất trong <head> — dùng cho GTM, GA4, Google Ads, mã xác minh site.', 'tl-site-caophat' ),
				),
				'body'   => array(
					__( 'Sau thẻ mở <body>', 'tl-site-caophat' ),
					__( 'Ngay sau thẻ mở body — thường là thẻ noscript của GTM / Meta Pixel.', 'tl-site-caophat' ),
				),
				'footer' => array(
					__( 'Cuối trang (footer)', 'tl-site-caophat' ),
					__( 'Trước </body>, sau mọi script khác — dùng cho chat widget hoặc script tải chậm.', 'tl-site-caophat' ),
				),
			);
			foreach ( $tlcp_pos as $tlcp_key => $tlcp_meta ) :
				?>
				<h3 style="margin:20px 0 4px;"><?php echo esc_html( $tlcp_meta[0] ); ?></h3>
				<textarea
					name="<?php echo esc_attr( TL_CP_TRACKING_OPTION . '[' . $tlcp_key . ']' ); ?>"
					rows="5"
					class="large-text code"
					spellcheck="false"
					placeholder="<!-- dán mã vào đây -->"
				><?php echo esc_textarea( $tlcp_code[ $tlcp_key ] ); ?></textarea>
				<p class="description"><?php echo esc_html( $tlcp_meta[1] ); ?></p>
			<?php endforeach; ?>
			<p class="description" style="color:#b32d2e;">
				<?php esc_html_e( 'Lưu ý: plugin này đang in sẵn 4 khối tracking ở đầu file (GTM / GA4 / Google Ads / Meta Pixel). Nếu dán mã TRÙNG ở đây thì số liệu sẽ BỊ ĐẾM ĐÔI — gỡ một trong hai chỗ.', 'tl-site-caophat' ); ?>
			</p>

			<hr style="margin:28px 0 0;">
			<h2><?php esc_html_e( 'Chế độ bảo trì', 'tl-site-caophat' ); ?></h2>
			<?php $tlcp_mt = tlcp_maintenance(); ?>
			<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin:8px 0 0;">
				<input type="checkbox" name="<?php echo esc_attr( TL_CP_MAINTENANCE_OPTION . '[on]' ); ?>" value="1" <?php checked( ! empty( $tlcp_mt['on'] ) ); ?>>
				<?php esc_html_e( 'Bật chế độ bảo trì', 'tl-site-caophat' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Khách CHƯA đăng nhập sẽ thấy trang thông báo với HTTP 503 + Retry-After (Google giữ trang trong index, không đánh rớt) + noindex, và trang bảo trì không bị cache.', 'tl-site-caophat' ); ?><br>
				<?php esc_html_e( 'Người có quyền quản trị vẫn xem web BÌNH THƯỜNG — bật bảo trì rồi vẫn sửa nội dung, cài plugin thoải mái.', 'tl-site-caophat' ); ?>
			</p>

			<h3 style="margin:20px 0 4px;"><?php esc_html_e( 'Nội dung thông báo', 'tl-site-caophat' ); ?></h3>
			<textarea name="<?php echo esc_attr( TL_CP_MAINTENANCE_OPTION . '[message]' ); ?>" rows="4" class="large-text code" spellcheck="false"><?php echo esc_textarea( (string) $tlcp_mt['message'] ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Để trống = dùng câu mặc định: “Website đang được bảo trì để nâng cấp. Vui lòng quay lại sau ít phút.”.', 'tl-site-caophat' ); ?><br>
				<?php esc_html_e( 'Cho phép HTML cơ bản (p, strong, br, a, ul/li…) — KHÔNG cho thẻ script.', 'tl-site-caophat' ); ?>
			</p>

			<?php tlcp_toc_settings_ui(); ?>

			<?php submit_button(); ?>

			<p class="tlcp-credit" style="margin-top:16px;color:#646970;font-style:italic;">
				<?php echo tlcp_credit_line(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- chuỗi đã escape từng phần, chỉ có 1 link cố định. ?>
			</p>
		</form>
	</div>
	<?php
}

/*
 * ---------------------------------------------------------------------------
 * CP3.3 — Đặt hàng nhanh.
 * Child theme in popup + gửi AJAX tới đây; phần dữ liệu/hành vi nằm ở plugin:
 * tạo ĐƠN WOOCOMMERCE THẬT, thanh toán COD, trạng thái "Đang xử lý" — giống luồng
 * checkout COD chuẩn (tự trừ tồn kho + gửi email "Đơn hàng mới" cho admin).
 * ---------------------------------------------------------------------------
 */

const TL_CP_QO_NONCE    = 'tlcp_quick_order';
const TL_CP_QO_MAX      = 5;  // số đơn tối đa / IP / 10 phút
const TL_CP_QO_WINDOW   = 600;
const TL_CP_QO_SHIPPING = 0;

/** Nonce cho popup (child theme gọi khi in form). */
function tlcp_quick_order_nonce(): string {
	return wp_create_nonce( TL_CP_QO_NONCE );
}

add_action( 'wp_ajax_nopriv_cp_quick_order', 'tlcp_quick_order_handle' );
add_action( 'wp_ajax_cp_quick_order', 'tlcp_quick_order_handle' );

/** AJAX: validate + tạo đơn, trả JSON cho popup. */
function tlcp_quick_order_handle(): void {
	/** Trả lỗi rồi dừng (wp_send_json_* tự exit). */
	$fail = static function ( string $message, int $code = 400 ): void {
		wp_send_json_error( array( 'message' => $message ), $code );
	};

	if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
		$fail( __( 'Yêu cầu không hợp lệ.', 'tl-site-caophat' ), 405 );
	}

	// 1. Nonce — chống gửi chéo site.
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( '' === $nonce || ! wp_verify_nonce( $nonce, TL_CP_QO_NONCE ) ) {
		$fail( __( 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang rồi gửi lại.', 'tl-site-caophat' ), 403 );
	}

	// 2. Honeypot — người thật không thấy field này.
	$honeypot = isset( $_POST['cp_hp'] ) ? trim( (string) wp_unslash( $_POST['cp_hp'] ) ) : '';
	if ( '' !== $honeypot ) {
		$fail( __( 'Không gửi được yêu cầu.', 'tl-site-caophat' ) );
	}

	// 3. Chống spam: giới hạn số đơn trên mỗi IP trong 10 phút.
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key   = 'tlcp_qo_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= TL_CP_QO_MAX ) {
		$fail( __( 'Bạn vừa gửi quá nhiều yêu cầu. Vui lòng gọi hotline để được hỗ trợ ngay.', 'tl-site-caophat' ), 429 );
	}
	set_transient( $key, $count + 1, TL_CP_QO_WINDOW );

	// 4. Dữ liệu khách nhập.
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$qty        = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 1;
	$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$phone      = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$address    = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
	$note       = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';

	$qty = ( $qty < 1 ) ? 1 : min( $qty, 99 );

	if ( mb_strlen( $name ) < 2 ) {
		$fail( __( 'Vui lòng nhập họ tên người nhận.', 'tl-site-caophat' ) );
	}
	if ( ! preg_match( '/^0\d{9,10}$/', (string) preg_replace( '/\D/', '', $phone ) ) ) {
		$fail( __( 'Số điện thoại chưa hợp lệ. Ví dụ: 0834.021.021', 'tl-site-caophat' ) );
	}
	if ( mb_strlen( $address ) < 8 ) {
		$fail( __( 'Vui lòng nhập địa chỉ nhận hàng cụ thể.', 'tl-site-caophat' ) );
	}

	// 5. Sản phẩm phải đang bán và đủ hàng (giá luôn lấy từ server, không tin client).
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
	if ( ! $product instanceof WC_Product || 'publish' !== get_post_status( $product_id ) || ! $product->is_purchasable() ) {
		$fail( __( 'Sản phẩm không còn bán. Vui lòng chọn sản phẩm khác.', 'tl-site-caophat' ) );
	}
	if ( ! $product->has_enough_stock( $qty ) ) {
		$fail( __( 'Sản phẩm hiện không đủ số lượng bạn cần. Vui lòng gọi hotline để kiểm tra kho.', 'tl-site-caophat' ) );
	}

	// 6. Tạo đơn.
	$order = wc_create_order( array( 'created_via' => 'cp-quick-order' ) );
	if ( is_wp_error( $order ) ) {
		$fail( __( 'Chưa tạo được đơn hàng. Vui lòng gọi hotline để đặt trực tiếp.', 'tl-site-caophat' ), 500 );
	}

	$order->add_product( $product, $qty );

	$addr = array(
		'first_name' => $name,
		'phone'      => $phone,
		'address_1'  => $address,
	);
	$order->set_address( $addr, 'billing' );
	$order->set_address( $addr, 'shipping' );
	$order->set_shipping_total( TL_CP_QO_SHIPPING );

	// Cổng thanh toán: dùng COD nếu đang bật; tắt thì vẫn lưu đơn, chỉ ghi nhãn.
	$gateways = ( function_exists( 'WC' ) && WC()->payment_gateways() )
		? WC()->payment_gateways()->get_available_payment_gateways()
		: array();
	if ( isset( $gateways['cod'] ) ) {
		$order->set_payment_method( $gateways['cod'] );
	} else {
		$order->set_payment_method_title( __( 'Trả tiền mặt khi nhận hàng', 'tl-site-caophat' ) );
	}

	if ( '' !== $note ) {
		$order->set_customer_note( $note );
	}
	$order->add_order_note( __( 'Đơn đặt nhanh từ popup "Mua hàng" (CP3.3) — khách tự nhập, không qua giỏ hàng.', 'tl-site-caophat' ) );
	$order->calculate_totals();
	$order->update_status( 'processing', __( 'Đặt nhanh từ popup (CP3.3).', 'tl-site-caophat' ), true );

	wp_send_json_success(
		array(
			'order'    => $order->get_order_number(),
			// CP3.3 — đặt thành công thì chuyển khách sang trang hoàn tất đơn (CP3.6), giống luồng checkout.
			// `get_checkout_order_received_url()` đã kèm `?key=<order_key>` nên trang đích tự xác thực được.
			'redirect' => $order->get_checkout_order_received_url(),
			'message'  => sprintf(
				/* translators: %s: mã đơn hàng */
				__( 'Đã tạo đơn #%s. Cao Phát sẽ gọi xác nhận trong ít phút.', 'tl-site-caophat' ),
				$order->get_order_number()
			),
		)
	);
}
