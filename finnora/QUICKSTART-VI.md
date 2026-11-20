# FINNORA 3.0 — Thư viện hình in và cá nhân hóa

## 1. Mở bản demo

Local → **web POD demo** → **Start site** → mở Chrome.

- Trang chủ: http://localhost:10004/
- Thư viện: http://localhost:10004/explore-designs/
- Tải ảnh riêng: http://localhost:10004/upload-your-photo/
- Bộ sưu tập Giáng sinh: http://localhost:10004/christmas-collection/
- Mua theo loại sản phẩm: http://localhost:10004/shop-native/

Nhấn **Ctrl + F5** nếu trình duyệt còn giữ giao diện cũ. Các địa chỉ localhost chỉ hoạt động trên máy đang chạy Local, không phải link công khai gửi khách.

## 2. Cấu trúc mới

Khách chọn **ý tưởng hình in trước**, rồi mới chọn món đồ để ứng dụng.

| Chủ đề | Các mẫu mở đầu |
|---|---|
| Funny & Quotes | Social Battery; Off-Duty Goose |
| People & Memories | Photo Story; Together at Christmas; Celebrate You |
| Pets & Animals | Pet Monarch; Festive Pet Club |
| Hobbies & Identity | Weekend Reader; Coffee & Code; Off-Duty Goose |
| Art & Patterns | Evergreen Lines; Color Current |
| Holidays & Celebrations | Gingerbread Wishes; Together at Christmas; Festive Pet Club; Evergreen Lines; Celebrate You |

Một mẫu có thể thuộc nhiều chủ đề nhưng chỉ có **một bản ghi**. Christmas Collection lấy 5 mẫu đã được chọn từ thư viện chung, không sao chép mẫu thành sản phẩm mới.

Ba loại dữ liệu được tách riêng trong WordPress:

- **Topics:** hình in nói về điều gì.
- **Styles:** cách thể hiện hình in.
- **Personalization:** khách được thêm ảnh, thêm chữ hay dùng nguyên mẫu.

7 nhóm sản phẩm và 9 sản phẩm cũ được giữ nguyên trong Products. Không đổi giá, SKU, tồn kho hoặc ID biến thể.

## 3. Thử luồng chọn thiết kế

1. Trang chủ → **Explore Designs**.
2. Chọn một chủ đề hoặc xem ngay các mẫu.
3. Cần thu hẹp thì dùng **Style**, **Personalization**, **Search designs** → **Show designs**.
4. Trên mobile: bấm **Filter designs** để mở các lựa chọn. Mặc định bảng đóng để nhường chỗ cho ảnh.
5. Mở **Social Battery** → **Compatible product** → chọn món đồ.
6. Chọn **Size / color / format**. Giá lấy từ biến thể WooCommerce thực tế.
7. **Make it yours** → nhập lời nhắn nếu muốn → chọn số lượng.
8. **Review your design** → kiểm tra hình, chữ, sản phẩm, màu, size và số lượng.
9. Chọn ô xác nhận → **Add to bag**.
10. Trong giỏ có tên mẫu, lời nhắn, biến thể, giá và **View saved design**.

**Item total** là tổng tiền hàng; vận chuyển và thuế, nếu có trong cấu hình WooCommerce, được tính riêng ở checkout. Bản demo vẫn sử dụng cơ chế thanh toán demo đã có.

## 4. Thử luồng dùng ảnh của mình

1. Trang chủ → **Upload Your Photo**.
2. **Choose a photo** → chọn JPG, PNG hoặc WebP, tối đa 8 MB và 24 megapixel.
3. Dùng **Zoom**, **Horizontal crop**, **Vertical crop** để chọn vùng ảnh.
4. **Choose your look:** Original photo hoặc Black & white.
5. **Choose your layout:** Photo Story hoặc Together at Christmas.
6. **Continue with this photo** → chọn sản phẩm → cá nhân hóa → xem lại.
7. Muốn chỉnh vùng ảnh: **Edit photo & crop**.
8. Kiểm tra ô xác nhận → **Add to bag**.

Ảnh được ghép/cắt thủ công trong trình duyệt. Không có chuyển đổi AI sang hoạt hình, màu nước hoặc chân dung sơn dầu. Pet Monarch là khung ảnh có vương miện, không phải AI vẽ lại thú cưng.

## 5. Bản nháp và sửa giỏ hàng

- Ảnh, lời nhắn và lựa chọn được lưu vào bản nháp của trình duyệt đang dùng.
- Tải lại trang hoặc quay lại vẫn phục hồi được bản nháp. Không tự đồng bộ sang máy/trình duyệt khác.
- **Clear my saved draft** xóa bản nháp đang mở. Có thể chọn ảnh mới ngay sau đó.
- Trong giỏ → **Open saved browser draft** → chỉnh → xem lại → thêm vào giỏ: mục cũ được thay bằng cấu hình mới.
- Nếu bản nháp đã bị xóa hoặc đang dùng trình duyệt khác, cần chọn lại ảnh/chữ trước khi cập nhật.

Ảnh chỉ được gửi lên website khi bấm **Add to bag**. Ảnh gốc và bản xem trước lưu ngoài thư mục web công khai; đường xem kiểm tra phiên người dùng hoặc quyền quản trị. Link xem có hạn 7 ngày; việc dọn file theo lịch cần WP-Cron hoạt động. Đây là cơ chế lưu cho demo, chưa phải kho file sản xuất dài hạn.

## 6. Chủ web quản lý mẫu

1. Local → **WP Admin**.
2. Menu trái → **Design Library** → mở một mẫu.
3. Sửa tiêu đề và đoạn mô tả bằng tiếng Anh.
4. **Featured image → Set featured image**: chọn ảnh artwork PNG/JPG/WebP để thay ảnh thư viện và ảnh dùng trong trình xem trước.
5. **Topics:** chọn một hoặc nhiều chủ đề.
6. **Styles:** gắn phong cách đúng với hình.
7. **Design configuration:** bật/tắt Allow a customer photo, Allow a name or message, Include in Christmas Collection.
8. **Compatible FINNORA product slugs:** nhập ví dụ `colour-tee,paint-mug,canvas-tote`.
9. **Update** → mở trang thư viện → Ctrl + F5.

Khả năng cá nhân hóa được cập nhật theo các ô cho phép ảnh/chữ. Mẫu mới cần có ảnh artwork hoặc tên Bundled artwork slug thực sự tồn tại trong theme. Các vùng ảnh của mẫu demo dùng bố cục cố định; mẫu có vùng cắt khác cần lập trình thêm vị trí trước khi dùng.

### Thêm sản phẩm làm nền in

Products → Add New → cấu hình sản phẩm, giá, tồn kho và biến thể → Inventory → đặt SKU dạng `FIN-ten-san-pham` → Publish. Trong Design Library → mẫu phù hợp → Compatible product slugs → thêm `ten-san-pham` → Update.

Không gắn mọi mẫu vào mọi món đồ. Kiểm tra tỉ lệ, vùng in và mẫu vật lý trước khi mở bán thật. Sản phẩm phải xuất bản, có giá và có biến thể mua được thì mới xuất hiện trong trình chọn.

### Tạo lại nội dung mẫu còn thiếu

Design Library → **Setup & guidance** → **Add missing sample designs and pages**. Không ghi đè các mẫu đã tồn tại.

## 7. Đã hoạt động và giới hạn

| Hạng mục | Trạng thái |
|---|---|
| 6 chủ đề, 12 mẫu, 5 mẫu Giáng sinh | Hoạt động trong WordPress |
| Lọc chủ đề/phong cách/cá nhân hóa, tìm kiếm, xóa bộ lọc | Hoạt động |
| Một card cho mỗi mẫu, dùng lại qua các collection | Hoạt động |
| Chọn sản phẩm/biến thể/giá WooCommerce | Hoạt động với danh sách tương thích của demo |
| Thêm chữ, tải ảnh, chỉnh vùng cắt, đen–trắng | Hoạt động trong trình duyệt |
| Lưu bản nháp và sửa mục trong giỏ | Hoạt động trên cùng trình duyệt |
| Lưu cấu hình và xem trước vào giỏ; chuyển cấu hình vào dòng đơn hàng | Đã lập trình; giỏ đã thử trực tiếp |
| Preview trên hình áo/cốc/túi | Sơ đồ 2D minh họa, không chứng minh độ vừa hoặc vị trí in cuối cùng |
| Gingerbread nâu tự nhiên, hai tay trên mép CTA | Đã có; nhún/gõ nhẹ theo đợt có nghỉ, hỗ trợ reduced-motion |
| AI cartoon/watercolor/portrait, dựng nhân vật nhiều tùy chọn | Chưa kết nối |
| File in đạt chuẩn DPI/bleed/màu, nhà in và giao hàng thật | Chưa tích hợp |
| Thanh toán thật, ưu đãi/bestseller và review thương mại | Không tự bật hoặc tạo giả |

12 mẫu là thư viện minh họa mở đầu, không phải cam kết đã đủ 48 mẫu thương mại. Muốn mở rộng cần thêm artwork và kiểm tra từng cấu hình sản phẩm.

## 8. Kiểm tra đã thực hiện

- 30 kiểm tra thư viện, lọc, sản phẩm tương thích, giá, tải ảnh, quyền xem ảnh và sửa giỏ.
- 20 kiểm tra mua sắm WooCommerce cũ, đã điều chỉnh kỳ vọng trang chủ theo cấu trúc mới.
- 20 lượt responsive: 5 màn hình × 1440/768/390/320px; không thấy tràn ngang hoặc ảnh hỏng ở thời điểm đo.
- Thử bằng giao diện: tải ảnh mẫu, đổi look/layout, thêm lời nhắn, xem lại, thêm vào giỏ; kiểm tra phục hồi và xóa bản nháp.
- Không tạo đơn thanh toán thật hoặc đánh giá khách hàng giả khi kiểm tra.

Đây là kiểm tra trong môi trường Local, không phải chứng nhận sẵn sàng bán hàng hoặc tuân thủ WCAG toàn bộ.

## 3.0.1 — Nhân vật Giáng sinh
- Thẻ sản phẩm WooCommerce và thẻ thiết kế: tuần lộc ôm quà hiện khi rê chuột, chạm hoặc focus bằng bàn phím. Chỉ một thẻ có nhân vật tại một thời điểm; nhân vật không chặn nút/link.
- Trang tải ảnh và mẫu hỗ trợ ảnh: ông già Noel cầm khung ảnh bên cạnh phần Choose a photo; gật nhẹ một lần sau khi ảnh tải thành công. Không chạy lại khi tự khôi phục bản nháp.
- Hai SVG gốc: assets/characters/reindeer-gift.svg và santa-photo.svg. Hiệu ứng: assets/holiday-characters.css; tương tác: assets/holiday-characters.js. Các hình là vector được vẽ cho dự án, không gọi dịch vụ AI.
- Mobile thu nhỏ nhân vật. prefers-reduced-motion tắt animation/transition. Không thêm âm thanh, popup hay thay đổi giá/sản phẩm.
- Kiểm tra: logic hover/focus/chạm/chỉ một thẻ, kết nối sự kiện ảnh thành công; PHP và JavaScript hợp lệ; trang tải ảnh desktop/mobile không tràn ngang và nhân vật không che ô chọn ảnh.
