> Current release: FINNORA4.1.0. Read ../README.md for current installation, layout and demo limitations. The versioned notes below describe earlier releases.

<!-- FINNORA 2.3: Appearance → Customize → FINNORA — Shopping layout controls headings, product count, homepage sections, and delivery/care/returns text. Category cards use WooCommerce categories and thumbnail images. -->
# FINNORA Studio — Theme WordPress 2.3 — Shopping layout

Bản 2.2 dùng câu chữ tiếng Anh. Khi nâng cấp từ dữ liệu mẫu cũ: Appearance → FINNORA setup → Apply English storefront copy. Hướng dẫn quản trị bên dưới được giữ bằng tiếng Việt để dễ tra cứu.

Theme độc lập, không cần Elementor, Sites hoặc tài khoản ChatGPT. Mã PHP, CSS, JavaScript và ảnh đi kèm nằm trong thư mục này. Không có URL cài đặt, tài khoản hay mật khẩu của dự án cũ trong theme.

**Mới ở 2.1:** kệ trưng bày, dải khám phá và phòng thử áo 2D. Vào **Sản phẩm → Chỉnh sửa → Dữ liệu sản phẩm → Chung → Trải nghiệm FINNORA** để chọn kiểu theo từng món. Áo mới cần URL ảnh PNG/WebP nền trong suốt; hai áo mẫu đã có ảnh minh họa. Camera/ảnh được xử lý trên thiết bị, không tự theo người và không xác định độ vừa.

## Cài bằng ZIP

1. WordPress → Giao diện (Appearance) → Giao diện (Themes) → Thêm mới (Add New) → Tải giao diện lên (Upload Theme).
2. Chọn `finnora-theme-2.3.0.zip` → Cài đặt (Install Now) → Kích hoạt (Activate).
3. Mở trang chủ: thiết kế FINNORA đã có sẵn. Theme tự thêm 4 trang mang tiền tố `finnora-`: Về FINNORA, Liên hệ, Ghi chép, Bộ sưu tập. Nội dung cũ không bị xóa.
4. Muốn chỉnh dữ liệu mẫu trong WordPress: Giao diện → Bắt đầu với FINNORA → Nhập nội dung mẫu FINNORA.

Nếu site đã có theme thư mục `finnora` bản cũ, hãy sao lưu site trước khi chọn thay thế theme bằng bản 2.1.

## Cài bằng thư mục

Copy **nguyên thư mục `finnora`** vào `wp-content/themes/` của website đích → WordPress → Giao diện → kích hoạt FINNORA Studio.

Với Local: chọn site → Site folder → `app/public/wp-content/themes/` → đặt thư mục `finnora` ở đây → WP Admin → Giao diện → kích hoạt.

Không cần copy cơ sở dữ liệu, plugin tùy chỉnh hoặc cấu hình của máy làm demo.

## Đủ các trang yêu cầu

| Trang | Template | Nội dung |
|---|---|---|
| Trang chủ | `front-page.php` | Hero, sản phẩm, nhóm sản phẩm, câu chuyện thương hiệu, bài viết |
| Giới thiệu | `page-about.php` | Câu chuyện FINNORA, ảnh xưởng, giá trị; chỉnh phần nội dung bằng trình soạn thảo |
| Liên hệ | `page-contact.php` | Thông tin liên hệ, biểu mẫu, câu hỏi thường gặp |
| Danh mục bài viết | `home.php`, `category.php`, `archive.php` | Bài viết WordPress, danh mục, phân trang |
| Bài viết chi tiết | `single.php` | Ảnh đại diện, nội dung, thẻ, trước/sau, bình luận theo cài đặt WordPress |
| Danh mục sản phẩm | `page-shop.php`, `woocommerce.php` | Lọc nhóm sản phẩm; danh mục, sắp xếp và phân trang WooCommerce |
| Sản phẩm chi tiết | `woocommerce.php` | Gallery, giá, biến thể, số lượng, giỏ hàng, tab và sản phẩm liên quan của WooCommerce |

Ngoài ra có tìm kiếm, trang 404, trang thông thường và định dạng giỏ hàng/thanh toán/tài khoản WooCommerce.

## Dùng khi chưa cài WooCommerce

Kích hoạt theme → trang chủ có 4 sản phẩm mẫu → Sản phẩm mở bộ sưu tập 9 món → bấm sản phẩm để xem chi tiết minh họa. Các trang này là mẫu trưng bày, không giả lập thanh toán hoặc tạo đơn hàng.

Giá ở bộ mẫu độc lập là USD. Tìm kiếm có thể tìm các sản phẩm minh họa. Khi có sản phẩm WooCommerce đã xuất bản, theme ưu tiên sản phẩm thật từ WordPress.

## Dùng WooCommerce và nội dung mẫu

1. Plugin (Plugins) → Cài mới (Add New) → tìm WooCommerce → Cài đặt → Kích hoạt.
2. Giao diện → Bắt đầu với FINNORA → đọc mô tả → Nhập nội dung mẫu FINNORA.
3. Bộ nhập tạo 3 bài viết, 9 sản phẩm có size/màu và ảnh trong Thư viện. Tạo các trang WooCommerce còn thiếu. Chạy lại không nhân đôi mục đã tạo; không ghi đè sản phẩm cùng SKU hoặc bài viết cùng slug đã có.
4. Sản phẩm → Tất cả sản phẩm → chọn sản phẩm → sửa tên, ảnh, mô tả, giá và biến thể → Cập nhật.
5. Bài viết → Tất cả bài viết → sửa các bài FINNORA; xóa bài “Hello world!” nếu không dùng. Danh mục bài viết lấy từ Bài viết → Chuyên mục.
6. WordPress dùng tiếng Anh thì các nút của WooCommerce có thể vẫn là tiếng Anh. Cài đặt → Tổng quan → Ngôn ngữ website → Tiếng Việt; cập nhật bản dịch của plugin khi cần.

**Để giống toàn bộ nội dung demo:** dùng một site thử nghiệm sạch, kích hoạt theme, cài WooCommerce và nhập mẫu. Trên site có dữ liệu sẵn, bố cục FINNORA áp dụng nhưng tên, ảnh, số lượng sản phẩm và bài viết lấy từ chính site đó.

Theme không thay tiền tệ, cổng thanh toán, thuế, vận chuyển, menu đã gán hoặc trang WooCommerce hợp lệ đã có. Giá số của sản phẩm nhập dùng theo đơn vị tiền tệ hiện tại của WooCommerce; kiểm tra lại trước khi dùng bán hàng.

Sản phẩm nhập là sản phẩm WooCommerce có thể đặt mua. Nếu website đã bật cổng thu tiền thật, những sản phẩm này cũng có thể đi vào thanh toán thật. Nên nhập mẫu trên site thử nghiệm; theme không vô hiệu hóa cổng thanh toán của site.

## Chỉnh thiết kế

- Logo: Giao diện → Tùy biến (Customize) → Nhận diện website (Site Identity) → Logo.
- Ảnh bìa, tiêu đề, mô tả, màu xanh nhấn, thông báo đầu trang: Giao diện → Tùy biến → FINNORA — Giao diện & liên hệ.
- Menu riêng: Giao diện → Menu → tạo menu → gán vào Menu chính FINNORA. Chưa gán: theme dùng menu mặc định.
- Menu chân trang: Giao diện → Menu → gán vào Menu chân trang FINNORA.
- Nội dung Giới thiệu: Trang → Về FINNORA → chỉnh phần nội dung → Cập nhật.
- Trang mới: Trang → Thêm mới → chọn mẫu FINNORA trong thuộc tính Template nếu muốn dùng bố cục Giới thiệu/Liên hệ/Ghi chép/Bộ sưu tập.
- Trang chủ dùng bố cục riêng của `front-page.php`; nội dung trang chủ cũ vẫn còn trong cơ sở dữ liệu. Theme không đổi các thiết lập Reading của website.

Menu được rê chuột hoặc dùng bàn phím sẽ mở danh mục; mục đang xem có màu xanh và gạch chân. Tìm kiếm ở hàng dưới; danh mục luôn ở lớp trên. Điện thoại dùng nút Menu, có thể đóng bằng Escape.

## Biểu mẫu liên hệ

1. Giao diện → Tùy biến → FINNORA → Email nhận liên hệ → nhập email của chủ website → Xuất bản.
2. Cấu hình gửi email/SMTP cho WordPress trên hosting của bạn.
3. Mở Liên hệ → nhập tên/email/lời nhắn → đồng ý gửi thông tin → Gửi lời nhắn.

Chưa cấu hình email: biểu mẫu hiển thị nhưng bị vô hiệu hóa, không báo gửi thành công giả. Form có kiểm tra dữ liệu phía máy chủ, nonce, ô chống bot và giới hạn tần suất. Nội dung không được lưu thành bài viết công khai.

`wp_mail()` tiếp nhận thư không đồng nghĩa thư đã đến hộp thư. Trong kiểm thử chưa xác minh phát thư qua SMTP bên ngoài. Local cần dịch vụ mail phù hợp hoặc công cụ xem email thử nghiệm.

## Lỗi thường gặp

- Không thấy theme: kiểm tra `wp-content/themes/finnora/style.css`; tránh lồng `finnora/finnora/`.
- Trang Shop hiện “Great things are on the horizon”: WooCommerce → Settings → Site visibility → kiểm tra Coming soon/Live. Theme tôn trọng lựa chọn hiển thị của chủ site.
- Sản phẩm không xuất hiện: Sản phẩm → kiểm tra đã Xuất bản và không bị ẩn khỏi danh mục; nhập mẫu nếu site còn trống.
- Menu/bài viết chưa giống ảnh: gán menu phù hợp, nhập nội dung mẫu hoặc dùng dữ liệu của bạn. Theme không thay toàn bộ dữ liệu của một site đã có.
- Trang trả 404 sau khi chuyển hosting: Cài đặt → Đường dẫn tĩnh (Permalinks) → Lưu thay đổi; kiểm tra cấu hình rewrite của máy chủ.
- Sửa CSS nhưng chưa thấy đổi: xóa cache của plugin/CDN và tải lại trình duyệt. Theme có gắn phiên bản theo thời gian sửa file.
- Không gửi được liên hệ: kiểm tra email nhận → cấu hình SMTP → xem nhật ký gửi thư; theme không thể tự cấp dịch vụ email.
- Link Local không mở bên ngoài: không gửi `localhost` hoặc tên miền `.local`. Local → site đang chạy → Tools → Live Links → On → gửi link và thông tin xem.
- Live Links báo On nhưng link 404: Off → chờ đóng tunnel → On → kiểm tra lại từ bên ngoài. Máy phải bật, có Internet; Live Links không thay thế hosting hoạt động liên tục.

## Phạm vi và yêu cầu

- WordPress 6.5+; PHP 8.0+. Đã chạy kiểm thử trên WordPress 7.1.3, PHP 8.2.29, MySQL 8.4.0; WooCommerce 11.2.1.
- Không có phụ thuộc plugin bắt buộc để hiển thị theme. WooCommerce cần cho quản lý sản phẩm, biến thể, giỏ hàng và đơn hàng thật.
- Không có AI thử đồ, camera AR, đồng bộ nhà in POD, thanh toán cấu hình sẵn hoặc cơ chế chia sẻ link tự động trong theme.
- Ảnh đi kèm là mockup minh họa của dự án; không chứng minh sản phẩm đã được sản xuất, độ vừa, chất liệu hoặc màu in thực tế.
- Cài thêm plugin cache, đa ngôn ngữ, page builder hoặc tùy biến hosting có thể làm thay đổi kết quả. Không thể bảo đảm theme tương thích với mọi tổ hợp plugin chưa kiểm thử.
- Không có mã theo dõi, phông chữ tải từ bên thứ ba hoặc dịch vụ AI được gọi từ theme.

## Tài liệu kỹ thuật tham khảo

- Phân cấp template WordPress: https://developer.wordpress.org/themes/classic-themes/basics/template-hierarchy/
- Template và hook WooCommerce: https://developer.woocommerce.com/docs/theming/theme-development/template-structure/

Theme sử dụng PHP template chuẩn và các template/hook hiện hành của WooCommerce; không đóng gói bản sao cũ của toàn bộ template plugin.

Mã theme được cung cấp theo GPL v2 hoặc mới hơn. Xem `LICENSE.txt`.
