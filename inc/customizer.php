<?php
/**
 * CP2.6 — Customizer cho khối "Danh mục nổi bật": chọn danh mục · kéo thả thứ tự · bố cục.
 *
 * Control tuỳ biến `CP_Term_Order_Control`: danh sách checkbox của TOÀN BỘ danh mục + kéo thả để
 * sắp thứ tự (dùng jQuery UI sortable có sẵn trong wp-admin). Giá trị setting là **CSV term_id
 * theo đúng thứ tự hiển thị** — chỉ gồm danh mục được tick.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'CP_Term_Order_Control' ) ) {

	/**
	 * CP2.6 — Control "chọn + kéo thả thứ tự" cho một taxonomy.
	 */
	class CP_Term_Order_Control extends WP_Customize_Control {

		/** @var string */
		public $type = 'cp_term_order';

		/** @var string Taxonomy hiển thị trong danh sách. */
		public $taxonomy = 'product_cat';

		/** @var string Nhãn cho mục "chưa chọn gì". */
		public $empty_label = '';

		/**
		 * In nội dung control.
		 */
		public function render_content(): void {
			$cp_selected = array_values( array_filter( array_map( 'absint', explode( ',', (string) $this->value() ) ) ) );
			$cp_terms    = get_terms(
				array(
					'taxonomy'   => $this->taxonomy,
					'hide_empty' => false,
					'orderby'    => 'name',
				)
			);
			if ( is_wp_error( $cp_terms ) ) {
				return;
			}

			// Danh mục ĐÃ CHỌN xếp trước (theo đúng thứ tự đã lưu), phần còn lại theo tên.
			$cp_ordered = array();
			foreach ( $cp_selected as $cp_id ) {
				foreach ( $cp_terms as $cp_term ) {
					if ( (int) $cp_term->term_id === $cp_id ) {
						$cp_ordered[] = $cp_term;
					}
				}
			}
			foreach ( $cp_terms as $cp_term ) {
				if ( ! in_array( (int) $cp_term->term_id, $cp_selected, true ) ) {
					$cp_ordered[] = $cp_term;
				}
			}
			?>
			<?php if ( $this->label ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>

			<ul class="cp-term-order">
				<?php foreach ( $cp_ordered as $cp_term ) : ?>
					<li class="cp-term-order__item">
						<label class="cp-term-order__label">
							<input type="checkbox" value="<?php echo esc_attr( (string) $cp_term->term_id ); ?>" <?php checked( in_array( (int) $cp_term->term_id, $cp_selected, true ) ); ?>>
							<span class="cp-term-order__name"><?php echo esc_html( $cp_term->name ); ?></span>
							<span class="cp-term-order__count">(<?php echo (int) $cp_term->count; ?>)</span>
						</label>
						<span class="cp-term-order__handle" aria-hidden="true" title="<?php esc_attr_e( 'Kéo để sắp thứ tự', 'tungleads-theme' ); ?>">⋮⋮</span>
					</li>
				<?php endforeach; ?>
			</ul>

			<input class="cp-term-order__value" type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $cp_selected ) ); ?>">
			<?php
		}
	}
}

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'CP_Text_Editor_Control' ) ) {

	/**
	 * CP1.3 — Control "ô soạn thảo văn bản" cho nội dung từng cột footer.
	 *
	 * In ra 1 `<textarea>` bình thường có `data-customize-setting-link` (nên nếu JS editor không chạy
	 * được thì vẫn gõ/sửa nội dung dạng text/HTML như thường), rồi `assets/customizer-footer.js` nâng
	 * cấp nó thành TinyMCE bằng `wp.editor.initialize()`. Giá trị lưu dạng HTML đã qua `wp_kses_post`.
	 */
	class CP_Text_Editor_Control extends WP_Customize_Control {

		/** @var string */
		public $type = 'cp_text_editor';

		/** @var int Số dòng textarea (chiều cao dự phòng khi TinyMCE chưa init). */
		public $rows = 7;

		/** @var string[] Nút trên thanh công cụ TinyMCE. */
		public $toolbar = array( 'bold', 'italic', 'bullist', 'numlist', 'link', 'unlink', 'undo', 'redo' );

		/**
		 * In nội dung control.
		 */
		public function render_content(): void {
			?>
			<?php if ( $this->label ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<textarea class="cp-editor" id="<?php echo esc_attr( 'cp-editor-' . $this->id ); ?>" rows="<?php echo esc_attr( (string) $this->rows ); ?>" data-cp-toolbar="<?php echo esc_attr( implode( ',', $this->toolbar ) ); ?>" <?php $this->link(); ?>><?php echo esc_textarea( (string) $this->value() ); ?></textarea>
			<?php
		}
	}
}

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'CP_Blocks_Repeater_Control' ) ) {

	/**
	 * CP2.4 + CP2.5 — Control "thêm/xoá/kéo thả nhiều khối" (repeater).
	 *
	 * Giá trị setting = **JSON** mảng các khối: `[{cat,title,order,count,layout}, …]` theo đúng thứ tự
	 * hiển thị. Nút "Thêm danh mục" tự clone 1 hàng trường nội dung (không giới hạn số khối).
	 */
	class CP_Blocks_Repeater_Control extends WP_Customize_Control {

		/** @var string */
		public $type = 'cp_blocks_repeater';

		/** @var string Taxonomy cho trường "danh mục". */
		public $taxonomy = 'product_cat';

		/** @var array<string,string> layout => nhãn. */
		public $layouts = array();

		/** @var array<string,string> order => nhãn. */
		public $orders = array();

		/** @var int */
		public $count_max = 20;

		/** @var int */
		public $count_default = 8;

		/** @var string */
		public $add_label = '';

		/** @var bool CP2.7 — có hiện mục "Sản phẩm ưu tiên" (chỉ khối sản phẩm)? */
		public $show_products = false;

		/**
		 * In 1 hàng khối (dùng cho cả hàng thật và hàng mẫu ẩn để JS clone).
		 *
		 * @param array<string,string>  $block Dữ liệu khối.
		 * @param WP_Term[]             $terms Danh mục để chọn.
		 * @param bool                  $is_tpl Hàng mẫu?
		 */
		private function cp_row( array $block, array $terms, bool $is_tpl = false ): void {
			$cp_cat = (string) ( $block['cat'] ?? '' );
			?>
			<div class="cp-block-row<?php echo $is_tpl ? ' cp-block-row--tpl' : ''; ?>" <?php echo $is_tpl ? 'hidden aria-hidden="true"' : ''; ?>>
				<div class="cp-block-row__head">
					<span class="cp-block-row__handle" title="<?php esc_attr_e( 'Kéo để đổi thứ tự khối', 'tungleads-theme' ); ?>">⋮⋮</span>
					<span class="cp-block-row__name"><?php esc_html_e( 'Khối', 'tungleads-theme' ); ?> <b class="cp-block-row__n">1</b></span>
					<button type="button" class="button-link cp-block-row__remove" aria-label="<?php esc_attr_e( 'Xoá khối', 'tungleads-theme' ); ?>">&times;</button>
				</div>
				<div class="cp-block-row__fields">
					<label class="cp-block-field">
						<span><?php esc_html_e( 'Danh mục', 'tungleads-theme' ); ?></span>
						<select data-field="cat">
							<option value=""><?php esc_html_e( '— Chọn danh mục —', 'tungleads-theme' ); ?></option>
							<?php foreach ( $terms as $cp_term ) : ?>
								<option value="<?php echo esc_attr( (string) $cp_term->term_id ); ?>" <?php selected( $cp_cat, (string) $cp_term->term_id ); ?>>
									<?php echo esc_html( ( $cp_term->parent ? '— ' : '' ) . $cp_term->name . ' (' . (int) $cp_term->count . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="cp-block-field">
						<span><?php esc_html_e( 'Tiêu đề (bỏ trống = tên danh mục)', 'tungleads-theme' ); ?></span>
						<input type="text" data-field="title" value="<?php echo esc_attr( (string) ( $block['title'] ?? '' ) ); ?>">
					</label>
					<div class="cp-block-field-row">
						<label class="cp-block-field">
							<span><?php esc_html_e( 'Sắp xếp', 'tungleads-theme' ); ?></span>
							<select data-field="order">
								<?php foreach ( $this->orders as $cp_key => $cp_label ) : ?>
									<option value="<?php echo esc_attr( (string) $cp_key ); ?>" <?php selected( (string) ( $block['order'] ?? 'date-desc' ), (string) $cp_key ); ?>><?php echo esc_html( $cp_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label class="cp-block-field">
							<span><?php esc_html_e( 'Số lượng', 'tungleads-theme' ); ?></span>
							<input type="number" data-field="count" min="1" max="<?php echo esc_attr( (string) $this->count_max ); ?>" step="1" value="<?php echo esc_attr( (string) ( $block['count'] ?? $this->count_default ) ); ?>">
						</label>
						<label class="cp-block-field">
							<span><?php esc_html_e( 'Bố cục', 'tungleads-theme' ); ?></span>
							<select data-field="layout">
								<?php foreach ( $this->layouts as $cp_key => $cp_label ) : ?>
									<option value="<?php echo esc_attr( (string) $cp_key ); ?>" <?php selected( (string) ( $block['layout'] ?? array_key_first( $this->layouts ) ), (string) $cp_key ); ?>><?php echo esc_html( $cp_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>
					<?php if ( $this->show_products ) : ?>
						<?php /* CP2.7 — chọn sản phẩm ưu tiên: danh sách tải qua AJAX theo danh mục của khối */ ?>
						<div class="cp-block-products">
							<button type="button" class="button-link cp-block-products__toggle" aria-expanded="false">
								<?php esc_html_e( 'Sản phẩm ưu tiên', 'tungleads-theme' ); ?>
								<span class="cp-block-products__count">(0)</span> ▾
							</button>
							<div class="cp-block-products__panel" hidden>
								<p class="cp-block-products__status"><?php esc_html_e( 'Bấm để tải danh sách sản phẩm của danh mục này.', 'tungleads-theme' ); ?></p>
								<ul class="cp-block-products__list"></ul>
							</div>
							<input type="hidden" data-field="products" value="<?php echo esc_attr( (string) ( $block['products'] ?? '' ) ); ?>">
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * In nội dung control.
		 */
		public function render_content(): void {
			$cp_terms = get_terms(
				array(
					'taxonomy'   => $this->taxonomy,
					'hide_empty' => false,
					'orderby'    => 'name',
				)
			);
			if ( is_wp_error( $cp_terms ) ) {
				$cp_terms = array();
			}

			$cp_blocks = json_decode( (string) $this->value(), true );
			$cp_blocks = is_array( $cp_blocks ) ? $cp_blocks : array();
			?>
			<?php if ( $this->label ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>

			<div class="cp-blocks">
				<div class="cp-blocks__list">
					<?php foreach ( $cp_blocks as $cp_block ) : ?>
						<?php $this->cp_row( is_array( $cp_block ) ? $cp_block : array(), $cp_terms ); ?>
					<?php endforeach; ?>
				</div>

				<button type="button" class="button button-secondary cp-blocks__add">
					<?php echo esc_html( $this->add_label ? $this->add_label : __( '+ Thêm danh mục', 'tungleads-theme' ) ); ?>
				</button>

				<div class="cp-blocks__tpl" hidden aria-hidden="true">
					<?php
					$this->cp_row(
						array(
							'cat'      => '',
							'title'    => '',
							'order'    => 'date-desc',
							'count'    => (string) $this->count_default,
							'layout'   => (string) array_key_first( $this->layouts ),
							'products' => '',
						),
						$cp_terms,
						true
					);
					?>
				</div>

				<input class="cp-blocks__value" type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( (string) $this->value() ); ?>">
			</div>
			<?php
		}
	}
}

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'CP_Header_Items_Control' ) ) {

	/**
	 * CP1.8 — Control "bật/tắt + kéo thả thứ tự" cho 6 mục của header.
	 *
	 * Danh sách cố định (logo · menu · search · hotline · giỏ · HTML): mỗi mục có 1 checkbox
	 * (bật/tắt — áp MỌI khổ màn hình) + tay nắm kéo thả (thứ tự — chỉ áp desktop >1024px).
	 * Giá trị setting = **CSV key theo đúng thứ tự hiển thị, chỉ gồm mục đang BẬT**
	 * (giống `CP_Term_Order_Control` của CP2.6) → `cp_header_items()` (`functions.php`) đọc lại.
	 */
	class CP_Header_Items_Control extends WP_Customize_Control {

		/** @var string */
		public $type = 'cp_header_items';

		/** @var array<string,string> key => nhãn hiển thị. */
		public $items = array();

		/** @var array<string,string> key => gợi ý ngắn bên cạnh nhãn. */
		public $hints = array();

		/**
		 * In nội dung control.
		 */
		public function render_content(): void {
			$cp_on      = cp_header_items();
			$cp_ordered = array();

			// Mục đang bật xếp trước theo đúng thứ tự đã lưu, phần còn lại theo thứ tự gốc.
			foreach ( $cp_on as $cp_key ) {
				if ( isset( $this->items[ $cp_key ] ) ) {
					$cp_ordered[] = $cp_key;
				}
			}
			foreach ( array_keys( $this->items ) as $cp_key ) {
				if ( ! in_array( $cp_key, $cp_ordered, true ) ) {
					$cp_ordered[] = $cp_key;
				}
			}
			?>
			<?php if ( $this->label ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>

			<ul class="cp-horder">
				<?php foreach ( $cp_ordered as $cp_key ) : ?>
					<?php $cp_is_on = in_array( $cp_key, $cp_on, true ); ?>
					<li class="cp-horder__item<?php echo $cp_is_on ? '' : ' cp-horder_off'; ?>">
						<label class="cp-horder__label">
							<input type="checkbox" value="<?php echo esc_attr( $cp_key ); ?>" <?php checked( $cp_is_on ); ?>>
							<span class="cp-horder__name"><?php echo esc_html( $this->items[ $cp_key ] ); ?></span>
							<?php if ( ! empty( $this->hints[ $cp_key ] ) ) : ?>
								<span class="cp-horder__hint"><?php echo esc_html( $this->hints[ $cp_key ] ); ?></span>
							<?php endif; ?>
						</label>
						<span class="cp-horder__handle" aria-hidden="true" title="<?php esc_attr_e( 'Kéo để sắp thứ tự', 'tungleads-theme' ); ?>">⋮⋮</span>
					</li>
				<?php endforeach; ?>
			</ul>

			<input class="cp-horder__value" type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $cp_on ) ); ?>">
			<?php
		}
	}
}

add_action(
	'customize_controls_enqueue_scripts',
	static function (): void {
		$cp_dir = get_stylesheet_directory();
		$cp_uri = get_stylesheet_directory_uri();
		$cp_ver = (string) wp_get_theme()->get( 'Version' );

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script(
			'cp-customizer-cats',
			$cp_uri . '/assets/customizer-cats.js',
			array( 'jquery', 'jquery-ui-sortable', 'customize-controls' ),
			file_exists( $cp_dir . '/assets/customizer-cats.js' ) ? (string) filemtime( $cp_dir . '/assets/customizer-cats.js' ) : $cp_ver,
			true
		);
		wp_enqueue_style(
			'cp-customizer-cats',
			$cp_uri . '/assets/customizer-cats.css',
			array(),
			file_exists( $cp_dir . '/assets/customizer-cats.css' ) ? (string) filemtime( $cp_dir . '/assets/customizer-cats.css' ) : $cp_ver
		);

		// CP2.4 + CP2.5 — repeater "thêm danh mục" (dùng chung jQuery UI sortable).
		wp_enqueue_script(
			'cp-customizer-blocks',
			$cp_uri . '/assets/customizer-blocks.js',
			array( 'jquery', 'jquery-ui-sortable', 'customize-controls' ),
			file_exists( $cp_dir . '/assets/customizer-blocks.js' ) ? (string) filemtime( $cp_dir . '/assets/customizer-blocks.js' ) : $cp_ver,
			true
		);
		/* CP2.7 — dữ liệu cho picker "Sản phẩm ưu tiên" (AJAX lấy sản phẩm theo danh mục). */
		wp_localize_script(
			'cp-customizer-blocks',
			'cpBlocksData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'cp_block_products' ),
				'i18n'    => array(
					'loading' => __( 'Đang tải sản phẩm…', 'tungleads-theme' ),
					'empty'   => __( 'Danh mục này chưa có sản phẩm nào.', 'tungleads-theme' ),
					'pickCat' => __( 'Chọn danh mục trước — danh sách sản phẩm sẽ hiện ở đây.', 'tungleads-theme' ),
					'outside' => __( 'khác danh mục', 'tungleads-theme' ),
					'error'   => __( 'Không tải được sản phẩm. Thử lại.', 'tungleads-theme' ),
				),
			)
		);
		wp_enqueue_style(
			'cp-customizer-blocks',
			$cp_uri . '/assets/customizer-blocks.css',
			array( 'cp-customizer-cats' ),
			file_exists( $cp_dir . '/assets/customizer-blocks.css' ) ? (string) filemtime( $cp_dir . '/assets/customizer-blocks.css' ) : $cp_ver
		);

		/* CP1.3 — mục "Footer Cao Phát": 4 ô soạn thảo của các cột cần TinyMCE. `wp_enqueue_editor()` là
		   hàm core để có `wp.editor.initialize()` + script TinyMCE (admin_print_footer_scripts có chạy
		   ở `customize.php`). JS của theme tự init khi section được mở (xem `customizer-footer.js`). */
		wp_enqueue_editor();
		wp_enqueue_script(
			'cp-customizer-footer',
			$cp_uri . '/assets/customizer-footer.js',
			array( 'jquery', 'customize-controls', 'editor' ),
			file_exists( $cp_dir . '/assets/customizer-footer.js' ) ? (string) filemtime( $cp_dir . '/assets/customizer-footer.js' ) : $cp_ver,
			true
		);

		// CP1.8 — control "bật/tắt + kéo thả thứ tự mục header" (dùng chung jQuery UI sortable).
		wp_enqueue_script(
			'cp-customizer-header',
			$cp_uri . '/assets/customizer-header.js',
			array( 'jquery', 'jquery-ui-sortable', 'customize-controls' ),
			file_exists( $cp_dir . '/assets/customizer-header.js' ) ? (string) filemtime( $cp_dir . '/assets/customizer-header.js' ) : $cp_ver,
			true
		);
		wp_enqueue_style(
			'cp-customizer-header',
			$cp_uri . '/assets/customizer-header.css',
			array(),
			file_exists( $cp_dir . '/assets/customizer-header.css' ) ? (string) filemtime( $cp_dir . '/assets/customizer-header.css' ) : $cp_ver
		);
	}
);

/**
 * CP2.6 — Section "Trang chủ — Danh mục nổi bật": chọn danh mục + thứ tự + bố cục.
 */
add_action(
	'customize_register',
	static function ( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section(
			'cp_home_cats',
			array(
				'title'       => __( 'Trang chủ — Danh mục nổi bật', 'tungleads-theme' ),
				'description' => __( 'Tick danh mục muốn hiển thị rồi KÉO THẢ để sắp thứ tự. Không tick gì = hiển thị tự động 6 danh mục nhiều sản phẩm nhất.', 'tungleads-theme' ),
				'priority'    => 33,
			)
		);

		/* CP2.6 — 2 dòng chữ của khối tiêu đề "Danh mục nổi bật" (yêu cầu Tùng 2026-09-15):
		   nhãn nhỏ (chip) + tiêu đề h2. Rỗng → `front-page.php` dùng lại mặc định i18n. */
		$wp_customize->add_setting(
			'cp_cats_label',
			array(
				'default'           => __( 'Danh mục nổi bật', 'tungleads-theme' ),
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'cp_cats_label',
			array(
				'section'     => 'cp_home_cats',
				'label'       => __( 'Nhãn nhỏ trên tiêu đề', 'tungleads-theme' ),
				'description' => __( 'Để trống sẽ dùng lại "Danh mục nổi bật".', 'tungleads-theme' ),
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'cp_cats_title',
			array(
				'default'           => __( 'Khám phá theo dòng sản phẩm', 'tungleads-theme' ),
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'cp_cats_title',
			array(
				'section'     => 'cp_home_cats',
				'label'       => __( 'Tiêu đề khối', 'tungleads-theme' ),
				'description' => __( 'Để trống sẽ dùng lại "Khám phá theo dòng sản phẩm".', 'tungleads-theme' ),
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'cp_cats_selected',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => static function ( $value ): string {
					$cp_ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
					return implode( ',', $cp_ids );
				},
			)
		);
		$wp_customize->add_control(
			new CP_Term_Order_Control(
				$wp_customize,
				'cp_cats_selected',
				array(
					'section'     => 'cp_home_cats',
					'label'       => __( 'Danh mục hiển thị (kéo thả để sắp thứ tự)', 'tungleads-theme' ),
					'description' => __( 'Thứ tự trên xuống = thứ tự trên trang chủ.', 'tungleads-theme' ),
					'taxonomy'    => 'product_cat',
					'settings'    => 'cp_cats_selected',
				)
			)
		);

		$wp_customize->add_setting(
			'cp_cats_layout',
			array(
				'default'           => 'cols-3',
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_key',
			)
		);
		$wp_customize->add_control(
			'cp_cats_layout',
			array(
				'section' => 'cp_home_cats',
				'label'   => __( 'Cách hiển thị', 'tungleads-theme' ),
				'type'    => 'select',
				'choices' => array(
					'cols-6'     => __( 'Lưới 6 cột', 'tungleads-theme' ),
					'cols-4'     => __( 'Lưới 4 cột', 'tungleads-theme' ),
					'cols-3'     => __( 'Lưới 3 cột', 'tungleads-theme' ),
					'horizontal' => __( 'Hiển thị ngang (cuộn)', 'tungleads-theme' ),
				),
			)
		);
	}
);

/**
 * CP2.4 + CP2.5 — Làm sạch giá trị repeater: JSON mảng khối, chỉ giữ trường hợp lệ.
 *
 * @param string   $value     JSON từ Customizer.
 * @param int      $count_max Số lượng tối đa mỗi khối.
 * @param string[] $layouts   Danh sách layout hợp lệ.
 * @return string JSON đã lọc ('' nếu không parse được).
 */
function cp_blocks_sanitize( string $value, int $count_max, array $layouts ): string {
	$cp_rows = json_decode( $value, true );
	if ( ! is_array( $cp_rows ) ) {
		return '';
	}

	$cp_clean = array();
	foreach ( $cp_rows as $cp_row ) {
		if ( ! is_array( $cp_row ) ) {
			continue;
		}

		/* CP2.7 — sản phẩm ưu tiên: CSV ID, tối đa 50 (giữ đúng thứ tự admin kéo thả). */
		$cp_picked = array_filter( array_map( 'absint', explode( ',', (string) ( $cp_row['products'] ?? '' ) ) ) );

		$cp_clean[] = array(
			'cat'      => (string) absint( $cp_row['cat'] ?? 0 ),
			'title'    => sanitize_text_field( (string) ( $cp_row['title'] ?? '' ) ),
			'order'    => in_array( $cp_row['order'] ?? '', array( 'date-desc', 'date-asc' ), true ) ? (string) $cp_row['order'] : 'date-desc',
			'count'    => (string) max( 1, min( $count_max, absint( $cp_row['count'] ?? 0 ) ) ),
			'layout'   => in_array( $cp_row['layout'] ?? '', $layouts, true ) ? (string) $cp_row['layout'] : (string) $layouts[0],
			'products' => implode( ',', array_slice( array_unique( $cp_picked ), 0, 50 ) ),
		);
	}

	return (string) wp_json_encode( $cp_clean );
}

/**
 * CP2.4 + CP2.5 — Section "khối theo danh mục" dạng REPEATER (thêm/xoá/kéo thả, không giới hạn).
 *
 * Thay cho 3 slot cố định trước đây: setting `cp_pblocks` (sản phẩm) và `cp_nblocks` (tin tức)
 * lưu JSON mảng khối — mỗi khối có danh mục · tiêu đề · sắp xếp · số lượng · bố cục.
 */
add_action(
	'customize_register',
	static function ( \WP_Customize_Manager $wp_customize ): void {
		$cp_orders   = array(
			'date-desc' => __( 'Mới nhất trước', 'tungleads-theme' ),
			'date-asc'  => __( 'Cũ nhất trước', 'tungleads-theme' ),
		);
		$cp_p_layout = array(
			'cols-4' => __( 'Lưới 4 cột', 'tungleads-theme' ),
			'cols-3' => __( 'Lưới 3 cột', 'tungleads-theme' ),
			'cols-2' => __( 'Lưới 2 cột', 'tungleads-theme' ),
			'scroll' => __( 'Cuộn ngang', 'tungleads-theme' ),
		);
		$cp_n_layout = array(
			'cols-3'   => __( 'Lưới 3 cột', 'tungleads-theme' ),
			'cols-2'   => __( 'Lưới 2 cột', 'tungleads-theme' ),
			'scroll'   => __( 'Cuộn ngang', 'tungleads-theme' ),
			/* CP2.5 — bố cục "Nổi bật": bài đầu = thẻ lớn bên TRÁI, các bài còn lại (Số lượng − 1) = danh sách bên PHẢI */
			'featured' => __( 'Nổi bật — 1 bài trái, còn lại bên phải', 'tungleads-theme' ),
		);

		$cp_defs = array(
			'products' => array(
				'section'       => 'cp_home_blocks_products',
				'section_title' => __( 'Trang chủ — Khối sản phẩm theo danh mục', 'tungleads-theme' ),
				'section_desc'  => __( 'Bấm "+ Thêm danh mục sản phẩm" để tạo khối mới, chọn danh mục rồi kéo thả để sắp thứ tự các khối. Khối chưa chọn danh mục sẽ không hiển thị.', 'tungleads-theme' ),
				'setting'       => 'cp_pblocks',
				'taxonomy'      => 'product_cat',
				'layouts'       => $cp_p_layout,
				'count_max'     => 20,
				'count_default' => 8,
				'add_label'     => __( '+ Thêm danh mục sản phẩm', 'tungleads-theme' ),
				'field_label'   => __( 'Các khối sản phẩm theo danh mục', 'tungleads-theme' ),
				'show_products' => true,
			),
			'news'     => array(
				'section'       => 'cp_home_blocks_news',
				'section_title' => __( 'Trang chủ — Khối tin tức theo chuyên mục', 'tungleads-theme' ),
				'section_desc'  => __( 'Bấm "+ Thêm chuyên mục tin tức" để tạo khối mới, chọn chuyên mục rồi kéo thả để sắp thứ tự các khối. Khối chưa chọn chuyên mục sẽ không hiển thị. Bố cục "Nổi bật": bài đầu hiển thị thành thẻ lớn bên trái, các bài còn lại nằm trong danh sách bên phải — "Số lượng" là TỔNG số bài (5 + 1 bài nổi bật = 6).', 'tungleads-theme' ),
				'setting'       => 'cp_nblocks',
				'taxonomy'      => 'category',
				'layouts'       => $cp_n_layout,
				'count_max'     => 12,
				'count_default' => 3,
				'add_label'     => __( '+ Thêm chuyên mục tin tức', 'tungleads-theme' ),
				'field_label'   => __( 'Các khối tin tức theo chuyên mục', 'tungleads-theme' ),
			),
		);

		foreach ( $cp_defs as $cp_key => $cp_def ) {
			$wp_customize->add_section(
				$cp_def['section'],
				array(
					'title'       => $cp_def['section_title'],
					'description' => $cp_def['section_desc'],
					'priority'    => 'products' === $cp_key ? 31 : 32,
				)
			);

			$wp_customize->add_setting(
				$cp_def['setting'],
				array(
					'default'           => '',
					'transport'         => 'refresh',
					'sanitize_callback' => static fn ( $value ): string => cp_blocks_sanitize(
						(string) $value,
						(int) $cp_def['count_max'],
						array_keys( (array) $cp_def['layouts'] )
					),
				)
			);

			$wp_customize->add_control(
				new CP_Blocks_Repeater_Control(
					$wp_customize,
					$cp_def['setting'],
					array(
						'section'       => $cp_def['section'],
						'label'         => $cp_def['field_label'],
						'settings'      => $cp_def['setting'],
						'taxonomy'      => $cp_def['taxonomy'],
						'layouts'       => $cp_def['layouts'],
						'orders'        => $cp_orders,
						'count_max'     => $cp_def['count_max'],
						'count_default' => $cp_def['count_default'],
						'add_label'     => $cp_def['add_label'],
						'show_products' => ! empty( $cp_def['show_products'] ),
					)
				)
			);
		}
	}
);

/**
 * CP2.7 — AJAX: danh sách sản phẩm của 1 danh mục, dùng cho picker "Sản phẩm ưu tiên".
 *
 * Trả `items` = [{id,title,outside}] (sản phẩm admin ĐÃ CHỌN nhưng không còn thuộc danh mục vẫn được
 * liệt kê với `outside = true` để không mất lựa chọn) + `chosen` = CSV ID đã chọn.
 * Guard: nonce `cp_block_products` + `current_user_can('edit_theme_options')`.
 */
add_action(
	'wp_ajax_cp_block_products',
	static function (): void {
		check_ajax_referer( 'cp_block_products', 'nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Bạn không đủ quyền.', 'tungleads-theme' ) ), 403 );
		}

		$cp_chosen = array_values( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( (string) ( $_POST['chosen'] ?? '' ) ) ) ) ) );
		$cp_cat    = absint( $_POST['cat'] ?? 0 );
		$cp_term   = $cp_cat ? get_term( $cp_cat, 'product_cat' ) : null;

		if ( ! $cp_term instanceof WP_Term || ! function_exists( 'wc_get_products' ) ) {
			wp_send_json_success(
				array(
					'items'  => array(),
					'chosen' => $cp_chosen,
				)
			);
		}

		$cp_products = wc_get_products(
			array(
				'status'   => 'publish',
				'limit'    => 200,
				'orderby'  => 'date',
				'order'    => 'date-asc' === sanitize_key( (string) ( $_POST['order'] ?? '' ) ) ? 'ASC' : 'DESC',
				'category' => array( $cp_term->slug ),
			)
		);

		$cp_items = array();
		$cp_seen  = array();
		foreach ( $cp_products as $cp_product ) {
			$cp_items[] = array(
				'id'      => $cp_product->get_id(),
				'title'   => $cp_product->get_name(),
				'outside' => false,
			);
			$cp_seen[]  = $cp_product->get_id();
		}

		foreach ( array_diff( $cp_chosen, $cp_seen ) as $cp_id ) {
			$cp_product = wc_get_product( $cp_id );
			if ( $cp_product ) {
				$cp_items[] = array(
					'id'      => $cp_id,
					'title'   => $cp_product->get_name(),
					'outside' => true,
				);
			}
		}

		wp_send_json_success(
			array(
				'items'  => $cp_items,
				'chosen' => $cp_chosen,
			)
		);
	}
);

/**
 * CP2.9 — Section "Trang chủ — Khối tab sản phẩm": 4 tab CỐ ĐỊNH (Mới · Bán chạy · Hot · Khuyến mãi).
 *
 * Mỗi tab 4 setting giống tinh thần "Sản phẩm theo danh mục" (CP2.4): bật/tắt · tiêu đề · số lượng ·
 * bố cục. Tên tab lấy từ `cp_product_tabs()` (`functions.php`) — 1 nguồn sự thật cho cả 3 nơi
 * (Customizer · sanitize · template part).
 */
add_action(
	'customize_register',
	static function ( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section(
			'cp_home_tabs',
			array(
				'title'       => __( 'Trang chủ — Khối tab sản phẩm', 'tungleads-theme' ),
				'description' => __( '4 tab cố định: Sản phẩm mới · Bán chạy · Hot (cờ "Nổi bật" của WooCommerce) · Khuyến mãi. Bỏ tick "hiển thị tab" để ẩn 1 tab; tab không có sản phẩm cũng tự ẩn.', 'tungleads-theme' ),
				'priority'    => 30,
			)
		);

		$cp_layouts = cp_tab_layouts();
		$cp_number  = 0;

		foreach ( cp_product_tabs() as $cp_source => $cp_tab ) {
			++$cp_number;
			$cp_key = 'cp_tab_' . $cp_source;

			/* translators: 1: số thứ tự tab, 2: tên tab */
			$cp_name = sprintf( __( 'Tab %1$d — %2$s', 'tungleads-theme' ), $cp_number, $cp_tab['title'] );

			$wp_customize->add_setting(
				$cp_key . '_show',
				array(
					'default'           => true,
					'transport'         => 'refresh',
					'sanitize_callback' => 'wp_validate_boolean',
				)
			);
			$wp_customize->add_control(
				$cp_key . '_show',
				array(
					'section' => 'cp_home_tabs',
					/* translators: %s: tên tab */
					'label'   => sprintf( __( '%s — hiển thị tab', 'tungleads-theme' ), $cp_name ),
					'type'    => 'checkbox',
				)
			);

			$wp_customize->add_setting(
				$cp_key . '_title',
				array(
					'default'           => $cp_tab['title'],
					'transport'         => 'refresh',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				$cp_key . '_title',
				array(
					'section'     => 'cp_home_tabs',
					/* translators: %s: tên tab */
					'label'       => sprintf( __( '%s — tiêu đề tab', 'tungleads-theme' ), $cp_name ),
					'description' => __( 'Để trống sẽ dùng lại tên mặc định.', 'tungleads-theme' ),
					'type'        => 'text',
				)
			);

			$wp_customize->add_setting(
				$cp_key . '_count',
				array(
					'default'           => 8,
					'transport'         => 'refresh',
					'sanitize_callback' => static fn ( $value ): int => max( 1, min( 20, absint( $value ) ) ),
				)
			);
			$wp_customize->add_control(
				$cp_key . '_count',
				array(
					'section'     => 'cp_home_tabs',
					/* translators: %s: tên tab */
					'label'       => sprintf( __( '%s — số sản phẩm (1–20)', 'tungleads-theme' ), $cp_name ),
					'type'        => 'number',
					'input_attrs' => array(
						'min'  => 1,
						'max'  => 20,
						'step' => 1,
					),
				)
			);

			$wp_customize->add_setting(
				$cp_key . '_layout',
				array(
					'default'           => 'cols-4',
					'transport'         => 'refresh',
					'sanitize_callback' => static function ( $value ) use ( $cp_layouts ): string {
						$value = sanitize_key( (string) $value );
						return array_key_exists( $value, $cp_layouts ) ? $value : 'cols-4';
					},
				)
			);
			$wp_customize->add_control(
				$cp_key . '_layout',
				array(
					'section' => 'cp_home_tabs',
					/* translators: %s: tên tab */
					'label'   => sprintf( __( '%s — bố cục', 'tungleads-theme' ), $cp_name ),
					'type'    => 'select',
					'choices' => $cp_layouts,
				)
			);
		}
	}
);

/**
 * CP1.3 — Section "Footer Cao Phát": nền (màu + ảnh + độ đậm) · số cột · nội dung từng cột · hotline.
 *
 * Mỗi cột = **tiêu đề + 1 ô soạn thảo văn bản** (control `CP_Text_Editor_Control` → TinyMCE), hoặc
 * chọn nguồn là menu "Footer" (Appearance → Menus). Ô để trống → `cp_footer_col_get()` /
 * `cp_footer_get()` (`functions.php`) trả lại mặc định nên `footer.php` không giữ chuỗi nào; `default`
 * của mỗi setting = đúng giá trị đang hiển thị để mở Customizer là sửa được ngay.
 */
add_action(
	'customize_register',
	static function ( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section(
			'cp_footer',
			array(
				'title'       => __( 'Footer Cao Phát', 'tungleads-theme' ),
				'description' => __( 'Nền footer (màu + ảnh nền, màu đè lên ảnh), số cột (3 hoặc 4) và nội dung từng cột: mỗi cột có tiêu đề + 1 ô soạn thảo văn bản. Ô để trống thì dùng lại mặc định.', 'tungleads-theme' ),
				'priority'    => 40,
			)
		);

		$cp_defaults = cp_footer_defaults();

		/* ---- Nền footer: màu + ảnh + độ đậm của LỚP MÀU đè lên ảnh ---- */
		$wp_customize->add_setting(
			'cp_footer_bg_color',
			array(
				'default'           => (string) ( $cp_defaults['bg_color'] ?? '#211d19' ),
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'cp_footer_bg_color',
				array(
					'section'     => 'cp_footer',
					'label'       => __( 'Màu nền footer', 'tungleads-theme' ),
					'description' => __( 'Màu này được vẽ ĐÈ LÊN ảnh nền — chỉnh độ đậm ở ô bên dưới.', 'tungleads-theme' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cp_footer_bg_image',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'cp_footer_bg_image',
				array(
					'section'     => 'cp_footer',
					'label'       => __( 'Ảnh nền footer (trống = chỉ dùng màu)', 'tungleads-theme' ),
					'description' => __( 'Ảnh trải kín footer, màu ở trên phủ lên ảnh.', 'tungleads-theme' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cp_footer_bg_opacity',
			array(
				'default'           => (int) ( $cp_defaults['bg_opacity'] ?? 70 ),
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): int => max( 0, min( 100, absint( $value ) ) ),
			)
		);
		$wp_customize->add_control(
			'cp_footer_bg_opacity',
			array(
				'section'     => 'cp_footer',
				'label'       => __( 'Độ đậm của màu trên ảnh nền (%)', 'tungleads-theme' ),
				'description' => __( '100% = chỉ thấy màu, 0% = chỉ thấy ảnh. Chỉ có tác dụng khi đã chọn ảnh nền.', 'tungleads-theme' ),
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 5,
				),
			)
		);

		/* ---- Số cột + logo ---- */
		$wp_customize->add_setting(
			'cp_footer_cols',
			array(
				'default'           => (string) ( $cp_defaults['cols'] ?? '4' ),
				'transport'         => 'refresh',
				'sanitize_callback' => static function ( $value ): string {
					$value = sanitize_key( (string) $value );
					return in_array( $value, array( '3', '4' ), true ) ? $value : '4';
				},
			)
		);
		$wp_customize->add_control(
			'cp_footer_cols',
			array(
				'section'     => 'cp_footer',
				'label'       => __( 'Số cột', 'tungleads-theme' ),
				'description' => __( 'Chọn 3 cột thì chỉ dùng Cột 1–3 (nội dung Cột 4 vẫn được giữ — đổi lại 4 là hiện ra).', 'tungleads-theme' ),
				'type'        => 'select',
				'choices'     => array(
					'4' => __( '4 cột', 'tungleads-theme' ),
					'3' => __( '3 cột', 'tungleads-theme' ),
				),
			)
		);

		$wp_customize->add_setting(
			'cp_footer_logo',
			array(
				'default'           => (bool) ( $cp_defaults['logo'] ?? true ),
				'transport'         => 'refresh',
				'sanitize_callback' => 'wp_validate_boolean',
			)
		);
		$wp_customize->add_control(
			'cp_footer_logo',
			array(
				'section' => 'cp_footer',
				'label'   => __( 'Hiện logo ở Cột 1 (logo đặt ở Site Identity)', 'tungleads-theme' ),
				'type'    => 'checkbox',
			)
		);

		/* ---- Từng cột: tiêu đề + nguồn nội dung + ô soạn thảo văn bản ---- */
		$cp_sources = array(
			'editor' => __( 'Ô soạn thảo văn bản', 'tungleads-theme' ),
			'menu'   => __( 'Menu “Footer” (Appearance → Menus)', 'tungleads-theme' ),
		);

		foreach ( cp_footer_columns_defaults() as $cp_n => $cp_col ) {
			$cp_key = 'cp_footer_col' . $cp_n;
			/* translators: %d: số thứ tự cột footer */
			$cp_name = sprintf( __( 'Cột %d', 'tungleads-theme' ), $cp_n );

			$wp_customize->add_setting(
				$cp_key . '_title',
				array(
					'default'           => (string) $cp_col['title'],
					'transport'         => 'refresh',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				$cp_key . '_title',
				array(
					'section' => 'cp_footer',
					/* translators: %s: tên cột, ví dụ "Cột 1" */
					'label'   => sprintf( __( '%s — tiêu đề (trống = không hiện)', 'tungleads-theme' ), $cp_name ),
					'type'    => 'text',
				)
			);

			$wp_customize->add_setting(
				$cp_key . '_source',
				array(
					'default'           => (string) $cp_col['source'],
					'transport'         => 'refresh',
					'sanitize_callback' => static function ( $value ) use ( $cp_sources ): string {
						$value = sanitize_key( (string) $value );
						return array_key_exists( $value, $cp_sources ) ? $value : 'editor';
					},
				)
			);
			$wp_customize->add_control(
				$cp_key . '_source',
				array(
					'section' => 'cp_footer',
					/* translators: %s: tên cột, ví dụ "Cột 1" */
					'label'   => sprintf( __( '%s — nguồn nội dung', 'tungleads-theme' ), $cp_name ),
					'type'    => 'select',
					'choices' => $cp_sources,
				)
			);

			$wp_customize->add_setting(
				$cp_key . '_content',
				array(
					'default'           => (string) $cp_col['content'],
					'transport'         => 'refresh',
					// CP1.3 — `wp_kses_post` + cho phép `<iframe>` nhúng bản đồ/video (host tin cậy).
					'sanitize_callback' => 'cp_footer_kses_content',
				)
			);
			$wp_customize->add_control(
				new \CP_Text_Editor_Control(
					$wp_customize,
					$cp_key . '_content',
					array(
						'section'     => 'cp_footer',
						/* translators: %s: tên cột, ví dụ "Cột 1" */
						'label'       => sprintf( __( '%s — nội dung', 'tungleads-theme' ), $cp_name ),
						'description' => __( 'Dùng được token: {hotline} · {hotline_tel} · {email} · {year} · {site}.', 'tungleads-theme' ),
						'settings'    => $cp_key . '_content',
					)
				)
			);
		}

		/* ---- Dòng cuối: bản quyền + ghi công ---- */
		$wp_customize->add_setting(
			'cp_footer_copyright',
			array(
				'default'           => (string) ( $cp_defaults['copyright'] ?? '' ),
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'cp_footer_copyright',
			array(
				'section'     => 'cp_footer',
				'label'       => __( 'Dòng cuối — bản quyền', 'tungleads-theme' ),
				'description' => __( 'Token: {year} = năm hiện tại · {site} = tên site · {hotline} · {email}. Trống = không hiện.', 'tungleads-theme' ),
				'type'        => 'text',
			)
		);

		/* CP1.3 — Dòng "Thiết kế bởi tungleads.com" KHÔNG còn ở đây: nay là markup CỐ ĐỊNH trong
		   `footer.php` (kèm link tungleads.com) theo yêu cầu Tùng 2026-09-16 → không sửa được từ admin. */

		/* Hotline TOÀN SITE — hiện ở topbar/header, footer và các nút "Gọi ngay" trong trang sản phẩm
		   (tất cả đều đi qua `cp_hotline_display()` / `cp_hotline_tel()`). Filter cùng tên vẫn ghi đè
		   được — không phá code/config đang dùng filter.
		   2026-09-16 (yêu cầu Tùng): 2 ô này CHUYỂN từ section "Footer Cao Phát" sang **"Trang chủ Cao Phát"**
		   (`cp_home`) — ID setting GIỮ NGUYÊN nên giá trị đã nhập không mất; đặt `priority => 90` để nằm
		   CUỐI danh sách của section đó (mặc định của các control còn lại là 10). */
		$wp_customize->add_setting(
			'cp_hotline_display',
			array(
				'default'           => '0834.021.021',
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'cp_hotline_display',
			array(
				'section'     => 'cp_home',
				'priority'    => 90,
				'label'       => __( 'Hotline — số hiển thị (toàn site)', 'tungleads-theme' ),
				'description' => __( 'Dùng cho topbar/header, footer và các nút “Gọi ngay” ở trang sản phẩm.', 'tungleads-theme' ),
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'cp_hotline_tel',
			array(
				'default'           => '0834021021',
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'cp_hotline_tel',
			array(
				'section'     => 'cp_home',
				'priority'    => 91,
				'label'       => __( 'Hotline — số để gọi (link tel:)', 'tungleads-theme' ),
				'description' => __( 'Khi tạo link gọi chỉ giữ chữ số và dấu +. Trống = 0834021021.', 'tungleads-theme' ),
				'type'        => 'text',
			)
		);
	}
);

/**
 * CP1.8 — Section "Header & Topbar Cao Phát": màu topbar/header · chiều cao header · cỡ logo ·
 * bật/tắt + kéo thả thứ tự 6 mục · 1 khối HTML cùng hàng.
 *
 * Mặc định lấy từ `cp_header_defaults()` (`functions.php`) — 1 nguồn sự thật cho cả 3 chỗ
 * (default của setting · fallback khi ô trống · tài liệu). Ô MÀU để trống = KHÔNG in CSS ⇒ giữ
 * nguyên màu gốc trong `caophat.css` (mở Customizer lần đầu không đổi gì so với trước).
 */
add_action(
	'customize_register',
	static function ( \WP_Customize_Manager $wp_customize ): void {
		$cp_defaults = cp_header_defaults();

		$wp_customize->add_section(
			'cp_header',
			array(
				'title'       => __( 'Header & Topbar Cao Phát', 'tungleads-theme' ),
				'description' => __( 'Màu sắc, chiều cao, cỡ logo và bố cục hàng header (logo · menu · ô tìm kiếm · tư vấn/hotline · giỏ hàng · khối HTML).', 'tungleads-theme' ),
				'priority'    => 25,
			)
		);

		/* ---------------------------------------------------------------- TOPBAR ---- */
		$wp_customize->add_setting(
			'cp_header_topbar_show',
			array(
				'default'           => $cp_defaults['topbar_show'] ? 1 : 0,
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): int => $value ? 1 : 0,
			)
		);
		$wp_customize->add_control(
			'cp_header_topbar_show',
			array(
				'section'     => 'cp_header',
				'label'       => __( 'Hiện thanh trên cùng (topbar)', 'tungleads-theme' ),
				'description' => __( 'Topbar vốn tự ẩn ở mobile (≤768px).', 'tungleads-theme' ),
				'type'        => 'checkbox',
			)
		);

		$wp_customize->add_setting(
			'cp_header_topbar_bg',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): string => (string) ( sanitize_hex_color( (string) $value ) ?? '' ),
			)
		);
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'cp_header_topbar_bg',
				array(
					'section'     => 'cp_header',
					'label'       => __( 'Topbar — màu nền', 'tungleads-theme' ),
					'description' => __( 'Để trống = giữ màu gốc (vàng). Bấm “Xoá” để về mặc định.', 'tungleads-theme' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cp_header_topbar_fg',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): string => (string) ( sanitize_hex_color( (string) $value ) ?? '' ),
			)
		);
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'cp_header_topbar_fg',
				array(
					'section'     => 'cp_header',
					'label'       => __( 'Topbar — màu chữ', 'tungleads-theme' ),
					'description' => __( 'Áp cho cả chữ và link (số hotline) trong topbar. Để trống = màu gốc.', 'tungleads-theme' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cp_header_topbar_items',
			array(
				'default'           => (string) $cp_defaults['topbar_items'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);
		$wp_customize->add_control(
			'cp_header_topbar_items',
			array(
				'section'     => 'cp_header',
				'label'       => __( 'Topbar — chữ bên trái (mỗi dòng 1 mục)', 'tungleads-theme' ),
				'description' => __( 'Xoá hết để ẩn phần chữ bên trái. Dòng trống bị bỏ qua.', 'tungleads-theme' ),
				'type'        => 'textarea',
				'input_attrs' => array( 'rows' => 4 ),
			)
		);

		$wp_customize->add_setting(
			'cp_header_topbar_label',
			array(
				'default'           => (string) $cp_defaults['topbar_label'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'cp_header_topbar_label',
			array(
				'section'     => 'cp_header',
				'label'       => __( 'Topbar — nhãn trước số hotline', 'tungleads-theme' ),
				'description' => __( 'Số hotline lấy từ "Trang chủ Cao Phát → Hotline toàn site".', 'tungleads-theme' ),
				'type'        => 'text',
			)
		);
	}
);

/**
 * CP1.8 (tiếp) — Section "Header & Topbar Cao Phát": phần HÀNG HEADER (nền · chiều cao · cỡ logo ·
 * bật/tắt + kéo thả 6 mục · khối HTML).
 *
 * Section này đăng ký ở 2 callback cho dễ đọc (topbar ở trên, hàng header ở đây) — WordPress cho
 * phép thêm setting/control vào section đã tạo.
 */
add_action(
	'customize_register',
	static function ( \WP_Customize_Manager $wp_customize ): void {
		$cp_defaults = cp_header_defaults();

		/* ---------------------------------------------------------------- NỀN + CHIỀU CAO ---- */
		$wp_customize->add_setting(
			'cp_header_header_bg',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): string => (string) ( sanitize_hex_color( (string) $value ) ?? '' ),
			)
		);
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'cp_header_header_bg',
				array(
					'section'     => 'cp_header',
					'label'       => __( 'Header — màu nền', 'tungleads-theme' ),
					'description' => __( 'Để trống = giữ nền trắng mờ gốc. Màu đặc sẽ làm mất hiệu ứng trong mờ (blur).', 'tungleads-theme' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cp_header_header_height',
			array(
				'default'           => (int) $cp_defaults['header_height'],
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): int => max( 56, min( 140, absint( $value ) ) ),
			)
		);
		$wp_customize->add_control(
			'cp_header_header_height',
			array(
				'section'     => 'cp_header',
				'label'       => __( 'Header — chiều cao (px)', 'tungleads-theme' ),
				'description' => __( 'Chiều cao tối thiểu của hàng header. Mặc định 76px.', 'tungleads-theme' ),
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 56,
					'max'  => 140,
					'step' => 2,
				),
			)
		);

		/* ---------------------------------------------------------------- CỠ LOGO ---- */
		$wp_customize->add_setting(
			'cp_header_logo_width',
			array(
				'default'           => (int) $cp_defaults['logo_width'],
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): int => max( 80, min( 400, absint( $value ) ) ),
			)
		);
		$wp_customize->add_control(
			'cp_header_logo_width',
			array(
				'section'     => 'cp_header',
				'label'       => __( 'Logo — độ lớn (px)', 'tungleads-theme' ),
				'description' => __( 'Kéo thanh để chỉnh bề ngang logo. Áp cho màn hình >768px; mobile giữ cỡ đã thiết kế (tối đa 160px).', 'tungleads-theme' ),
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 80,
					'max'  => 400,
					'step' => 5,
				),
			)
		);

		/* ------------------------------------------------- BỐ CỤC: BẬT/TẮT + KÉO THẢ ---- */
		$wp_customize->add_setting(
			'cp_header_items',
			array(
				'default'           => (string) $cp_defaults['items'],
				'transport'         => 'refresh',
				'sanitize_callback' => static function ( $value ): string {
					$cp_known = array_keys( cp_header_items_all() );
					$cp_out   = array();
					foreach ( explode( ',', (string) $value ) as $cp_key ) {
						$cp_key = sanitize_key( $cp_key );
						if ( in_array( $cp_key, $cp_known, true ) && ! in_array( $cp_key, $cp_out, true ) ) {
							$cp_out[] = $cp_key;
						}
					}

					return implode( ',', $cp_out );
				},
			)
		);
		$wp_customize->add_control(
			new \CP_Header_Items_Control(
				$wp_customize,
				'cp_header_items',
				array(
					'section'     => 'cp_header',
					'label'       => __( 'Các mục trên hàng header — bật/tắt & kéo thả thứ tự', 'tungleads-theme' ),
					'description' => __( 'Tích để HIỆN (áp mọi khổ màn hình). Kéo ⋮⋮ để đổi thứ tự — thứ tự chỉ áp cho desktop >1024px; tablet/mobile giữ bố cục đã thiết kế.', 'tungleads-theme' ),
					'items'       => cp_header_items_all(),
					'hints'       => array(
						'logo'    => __( 'ảnh logo', 'tungleads-theme' ),
						'menu'    => __( 'thanh menu chính', 'tungleads-theme' ),
						'search'  => __( 'ô tìm kiếm sản phẩm', 'tungleads-theme' ),
						'hotline' => __( 'số tư vấn', 'tungleads-theme' ),
						'cart'    => __( 'icon giỏ hàng', 'tungleads-theme' ),
						'html'    => __( 'dữ liệu HTML ở dưới', 'tungleads-theme' ),
					),
				)
			)
		);

		/* ---------------------------------------------------------------- KHỐI HTML ---- */
		$wp_customize->add_setting(
			'cp_header_html',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): string => (string) wp_kses_post( (string) $value ),
			)
		);
		$wp_customize->add_control(
			'cp_header_html',
			array(
				'section'     => 'cp_header',
				'label'       => __( 'Dữ liệu HTML thêm vào header', 'tungleads-theme' ),
				'description' => __( 'Ví dụ: <b>Gọi ngay 0834.021.021</b> hoặc <a href="#">Nhận báo giá</a>. Nằm CÙNG HÀNG với logo/menu/search/tư vấn/giỏ (vị trí do mục “Khối HTML” ở trên quyết định).', 'tungleads-theme' ),
				'type'        => 'textarea',
				'input_attrs' => array( 'rows' => 3 ),
			)
		);

		$wp_customize->add_setting(
			'cp_header_html_desktop_only',
			array(
				'default'           => $cp_defaults['html_desktop_only'] ? 1 : 0,
				'transport'         => 'refresh',
				'sanitize_callback' => static fn ( $value ): int => $value ? 1 : 0,
			)
		);
		$wp_customize->add_control(
			'cp_header_html_desktop_only',
			array(
				'section'     => 'cp_header',
				'label'       => __( 'Khối HTML — chỉ hiện ở desktop (>1024px)', 'tungleads-theme' ),
				'description' => __( 'Bật (mặc định) để không phá bố cục tablet/mobile. Tắt thì khối HTML hiện ở mọi khổ.', 'tungleads-theme' ),
				'type'        => 'checkbox',
			)
		);
	}
);

/**
 * CP2.10 — Section "4 cụm nổi bật Cao Phát": 4 cụm (tiêu đề + mô tả + icon) của dải nổi bật trang chủ.
 *
 * Mỗi cụm 3 control: TIÊU ĐỀ (text) · MÔ TẢ (text) · ICON (`WP_Customize_Media_Control` → attachment
 * ID; upload ảnh thì ảnh THAY icon SVG mặc định). Mặc định lấy `cp_features_defaults()`
 * (`functions.php`) — ô để trống dùng lại mặc định; riêng **cụm 4 “Báo giá 24/7”: mô tả để trống =
 * tự lấy số hotline toàn site** (Customizer → “Trang chủ Cao Phát”) nên phải để `default => ''`.
 */
add_action(
	'customize_register',
	static function ( \WP_Customize_Manager $wp_customize ): void {
		$cp_defaults = cp_features_defaults();

		$wp_customize->add_section(
			'cp_features',
			array(
				'title'       => __( '4 cụm nổi bật Cao Phát', 'tungleads-theme' ),
				'description' => __( 'Dải 4 cụm nổi bật trên trang chủ (dưới khối hero). Mỗi cụm sửa được tiêu đề + mô tả, và có thể tải ảnh icon lên để THAY icon mặc định. Ô để trống thì dùng lại mặc định.', 'tungleads-theme' ),
				'priority'    => 36,
			)
		);

		for ( $cp_n = 1; $cp_n <= 4; $cp_n++ ) {
			$cp_key = 'cp_feature' . $cp_n;

			// Tiêu đề.
			$wp_customize->add_setting(
				$cp_key . '_title',
				array(
					'default'           => (string) $cp_defaults[ $cp_n ]['title'],
					'transport'         => 'refresh',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				$cp_key . '_title',
				array(
					'section'     => 'cp_features',
					'priority'    => $cp_n * 10 + 1,
					/* translators: %d: số thứ tự cụm (1–4). */
					'label'       => sprintf( __( 'Cụm %d — tiêu đề', 'tungleads-theme' ), $cp_n ),
					'type'        => 'text',
					'input_attrs' => array( 'placeholder' => (string) $cp_defaults[ $cp_n ]['title'] ),
				)
			);

			// Mô tả (cụm 4 để trống = lấy số hotline toàn site).
			$wp_customize->add_setting(
				$cp_key . '_text',
				array(
					'default'           => 4 === $cp_n ? '' : (string) $cp_defaults[ $cp_n ]['text'],
					'transport'         => 'refresh',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				$cp_key . '_text',
				array(
					'section'     => 'cp_features',
					'priority'    => $cp_n * 10 + 2,
					/* translators: %d: số thứ tự cụm (1–4). */
					'label'       => sprintf( __( 'Cụm %d — mô tả', 'tungleads-theme' ), $cp_n ),
					'description' => 4 === $cp_n
						? __( 'Để trống = tự lấy số hotline toàn site (Customizer → “Trang chủ Cao Phát” → “Hotline — số hiển thị”).', 'tungleads-theme' )
						: '',
					'type'        => 'text',
					'input_attrs' => array( 'placeholder' => (string) $cp_defaults[ $cp_n ]['text'] ),
				)
			);

			// Icon (upload thay icon mặc định).
			$wp_customize->add_setting(
				$cp_key . '_icon',
				array(
					'default'           => 0,
					'transport'         => 'refresh',
					'sanitize_callback' => static fn ( $value ): int => absint( $value ),
				)
			);
			$wp_customize->add_control(
				new \WP_Customize_Media_Control(
					$wp_customize,
					$cp_key . '_icon',
					array(
						'section'     => 'cp_features',
						'priority'    => $cp_n * 10 + 3,
						'mime_type'   => 'image',
						/* translators: %d: số thứ tự cụm (1–4). */
						'label'       => sprintf( __( 'Cụm %d — icon (ảnh thay icon mặc định)', 'tungleads-theme' ), $cp_n ),
						'description' => __( 'Không chọn ảnh = dùng icon mặc định. Ảnh nên là PNG/SVG nền trong suốt, cạnh ~100px.', 'tungleads-theme' ),
					)
				)
			);
		}
	}
);

