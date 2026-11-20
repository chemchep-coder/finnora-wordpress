# FINNORA Studio — WordPress theme 4.1.0

Theme WordPress cho dự án học tập FINNORA: cửa hàng thời trang và quà tặng POD phong cách Christmas, giao diện tiếng Anh, forest green / white / Christmas red.

## Website, mã nguồn và bản xem

- Repository này chứa mã nguồn theme và ảnh minh họa. Đây không phải website được host trên GitHub Pages.
- Website WordPress phải chạy trên hosting có PHP/database hoặc trong Local. Đưa mã lên GitHub không tự cập nhật website đang chạy.
- Bản 4.0 có trong source; việc đưa lên domain phải được thực hiện và kiểm tra riêng.

## Cài theme

1. Tải repository: **Code → Download ZIP**, giải nén.
2. Copy thư mục **finnora** vào `wp-content/themes/` của WordPress.
3. WordPress → **Appearance → Themes → FINNORA Studio → Activate**.
4. Để cài qua **Upload Theme**, nén riêng thư mục `finnora` thành ZIP. Không tải ZIP của toàn repository lên làm theme.
5. Cài WooCommerce nếu cần danh mục, biến thể, giỏ hàng và checkout. Thiết lập cửa hàng, cổng thanh toán và vận chuyển riêng.
6. Sao lưu website trước khi thay bản theme đang dùng. Theme có các công cụ tạo nội dung mẫu; đọc hướng dẫn trong quản trị trước khi chạy.

Yêu cầu khai báo của theme: WordPress6.5+, PHP8.0+. Cần kiểm tra thực tế với phiên bản WordPress/WooCommerce của hosting; không coi nhãn `Tested up to` là bảo đảm tương thích.

## Quản lý

- **Appearance → Customize**: câu chữ trang chủ, hỗ trợ và đường dẫn các trang chính sách.
- **Appearance → Menus**: chỉnh menu. Menu tự gán được ưu tiên hơn menu mặc định của theme.
- **Products**: tên, giá, ảnh, danh mục và biến thể.
- **Appearance → Store readiness**: xem những mục cần hoàn thiện trước khi bán thật.
- Tạo và xuất bản chính sách đúng thực tế trước khi chọn trong **FINNORA — Store policies**.

## Trạng thái demo — cần đọc

Ảnh AI và sản phẩm mẫu chưa chứng minh vật liệu, kỹ thuật in, bảng size, chất lượng hay khả năng sản xuất/giao nhận. Theme không kết nối sẵn nhà cung cấp POD hoặc thanh toán thật. Bản xem2D không đo cơ thể và không bảo đảm độ vừa.

Không gửi sản phẩm concept lên Merchant Center như hàng bán thật. Sản phẩm SKU `FIN-` chưa xác minh bị loại khỏi Product/Offer structured data và đặt noindex trong theme. **Điều này không tự loại sản phẩm khỏi feed của plugin khác**; phải cấu hình feed riêng. `Supplier verified` không đồng nghĩa được Google duyệt.

Đánh giá hư cấu không được hiển thị trên trang chủ. Cần xác nhận thông tin doanh nghiệp, email hỗ trợ, chính sách, quốc gia giao hàng và checkout trước khi vận hành thật.

## Cấu trúc

```text
finnora/        Theme cài vào WordPress
tests/          Kiểm tra logic Merchant/concept bằng PHP, không cần database
README.md       Hướng dẫn phiên bản hiện tại
LICENSE.txt     Giấy phép mã nguồn
```

## Kiểm tra nhanh

```sh
php tests/merchant-readiness.php
php tests/shopping-context.php
```

Kiểm tra này không thay thế kiểm thử WordPress/WooCommerce, thiết bị di động, thanh toán hay giao nhận. PHP/CSS/JS đã được kiểm tra cục bộ trước commit đầu tiên.

## Bảo mật và tài nguyên

Repository không bao gồm WordPress core, database, file cấu hình hosting, mật khẩu, khóa API hoặc ảnh khách tải lên. Không commit các dữ liệu đó trong các lần sửa sau. Nguồn gốc tài nguyên được ghi tại `finnora/ASSET-PROVENANCE.md`.
