# Dự án Quản lý Sinh viên
Dự án hướng tới quy mô triển khai tại cấp Khoa (Khoa Công nghệ Thông tin), tập trung số hóa quy trình quản lý điểm và hồ sơ học thuật trên nền tảng Web.

## Bước 1: Phân tích vấn đề và Xác định phạm vi hệ thống

### 1.1. Bối cảnh và Quy trình hiện tại (As-Is)
Hiện tại, công tác quản lý điểm tại khoa đang được thực hiện bán thủ công, chủ yếu dựa trên Microsoft Excel. Đầu học kỳ, Giáo vụ xuất danh sách sinh viên từ hệ thống quản lý chung và gửi file Excel thủ công cho từng Giảng viên bộ môn. Cuối kỳ, Giảng viên tự tính điểm tổng kết bằng công thức Excel cá nhân, in bản cứng để ký xác nhận và gửi bản mềm (file Excel) cho Giáo vụ. Cuối cùng, Giáo vụ thu thập, đối chiếu và tổng hợp nhiều file rời rạc từ các môn học vào một tệp dữ liệu trung tâm (Master file) để tính GPA và xét cảnh báo học vụ.

### 1.2. Các vấn đề tồn đọng (Pain Points)
- Rủi ro toàn vẹn dữ liệu: Sao chép thủ công từ nhiều file dễ sai sót; thiếu cơ chế lưu vết chỉnh sửa (Audit Trail) nên khó truy xuất trách nhiệm khi có sai lệch điểm số.
- Nút thắt thời gian (Bottleneck): Tổng hợp điểm và tính GPA thủ công làm tiêu tốn hàng tuần, khiến sinh viên nhận kết quả muộn.
- Thiếu đồng bộ logic tính toán: Công thức tính điểm tổng phụ thuộc từng Giảng viên, dễ sai tỷ lệ và không có cơ chế validate tự động.

### 1.3. Đối tượng chịu ảnh hưởng
- Giáo vụ (Người quản lý): Quá tải vào cuối kỳ do xử lý nhiều file; chịu áp lực về độ chính xác khi tổng hợp và tính GPA cho hàng ngàn hồ sơ.
- Giảng viên: Tốn thời gian hành chính; khó theo dõi realtime danh sách sinh viên không đủ điều kiện dự thi.
- Sinh viên: Thiếu cập nhật kịp thời nên khó điều chỉnh kế hoạch học tập, tăng rủi ro bị cảnh báo học vụ.

### 1.4. Mục tiêu và Giải pháp (To-Be)
Hệ thống Quản lý Sinh viên cấp Khoa là nền tảng tập trung (Centralized Platform) trên Web với 3 giá trị cốt lõi:
- Tự động hóa toàn diện: Tự áp dụng trọng số để tính điểm tổng kết và quy đổi GPA từ tham số gốc, loại bỏ sai sót do thao tác thủ công.
- Kiểm soát quy trình chặt chẽ: Luồng trạng thái rõ ràng (Nhập điểm – Gửi duyệt – Khóa điểm), phân quyền nghiêm ngặt, tự động ghi log mọi thay đổi (Audit).
- Minh bạch và realtime: Sinh viên tra cứu trực tuyến, cập nhật điểm ngay khi Giáo vụ hoàn tất duyệt.

### 1.5. Phạm vi hệ thống (Scope)
- In-scope: Quản lý hồ sơ học thuật cơ bản; quản lý lớp học phần; số hóa quy trình nhập/duyệt điểm; tự động tính GPA (hệ 10 và hệ 4); phân loại học lực; xuất báo cáo thống kê kết quả học tập.
- Out-of-scope: Không bao gồm phân hệ thu/chi học phí; không hỗ trợ xếp thời khóa biểu tự động; không tích hợp E-learning (giao bài, nộp tiểu luận); không quản lý điểm rèn luyện ngoại khóa.

## Bước 2: Xác định tác nhân của hệ thống

- Admin - Quản trị hệ thống
    - Quản lý tài khoản.
    - Quản lý vai trò người dùng.
    - Thống kê trạng thái hoạt động của hệ thống.
- Giáo vụ
    - Phụ trách quản lý chung.
    - Quản lý hồ sơ sinh viên.
    - Thêm/sửa/xóa sinh viên ở các khóa học.
    - Phân công giảng viên dạy.
    - Xem kết quả tổng hợp, tính GPA.
    - Xuất báo cáo xếp loại học lực.
- Giảng viên bộ môn
    - Xem danh sách sinh viên thuộc lớp mình phụ trách.
    - Nhập điểm/sửa điểm trong thời gian cho phép.
- Sinh viên
    - Xem thông tin cá nhân của mình.
    - Tra cứu điểm số: xem điểm từng môn, điểm GPA và xếp loại học lực cá nhân.

## Bước 3: Danh mục toàn bộ các Use Case của hệ thống

### Nhóm 1: Quản trị Hệ thống & Tài khoản (System Admin)
- UC1.1: Đăng nhập / Đăng xuất hệ thống (Tất cả người dùng).
- UC1.2: Đổi mật khẩu cá nhân (Tất cả người dùng).
- UC1.3: Thêm mới tài khoản người dùng (Admin).
- UC1.4: Khóa / Mở khóa tài khoản (Admin).
- UC1.5: Reset mật khẩu cấp hệ thống (Admin).

### Nhóm 2: Quản lý Danh mục Cơ sở (Catalog Management)
- UC2.1: Quản lý Khoa/Bộ môn (Thêm, Sửa, Xóa) (Giáo vụ).
- UC2.2: Quản lý Niên khóa và Lớp sinh hoạt (Thêm, Sửa, Xóa) (Giáo vụ).
- UC2.3: Quản lý Học kỳ (Thêm mới, Đặt làm học kỳ hiện tại) (Giáo vụ).
- UC2.4: Quản lý Môn học (Khai báo mã môn, tên môn, số tín chỉ) (Giáo vụ).

### Nhóm 3: Quản lý Hồ sơ (Profile Management)
- UC3.1: Quản lý hồ sơ Giảng viên (Thêm, Sửa, Xóa, Cập nhật thông tin học hàm/học vị) (Giáo vụ).
- UC3.2: Quản lý hồ sơ Sinh viên (Thêm, Sửa, Xóa, Chuyển lớp sinh hoạt) (Giáo vụ).
- UC3.3: Xem thông tin hồ sơ cá nhân (Sinh viên, Giảng viên).

### Nhóm 4: Kế hoạch Đào tạo & Lớp học phần
- UC4.1: Mở Lớp học phần (LHP) & Thiết lập tỷ lệ % điểm thành phần (CC, GK, CK) (Giáo vụ).
- UC4.2: Phân công Giảng viên phụ trách LHP (Giáo vụ).
- UC4.3: Quản lý danh sách sinh viên tham gia LHP (Thêm/Xóa sinh viên vào lớp) (Giáo vụ).
- UC4.4: Hủy phân công / Đóng LHP (Giáo vụ).

### Nhóm 5: Quản lý Điểm số (Quy trình cốt lõi)
- UC5.1: Mở / Khóa cổng nhập điểm theo Học kỳ hoặc LHP (Giáo vụ).
- UC5.2: Xem danh sách LHP được phân công giảng dạy (Giảng viên).
- UC5.3: Nhập điểm thành phần (CC, GK, CK) & Lưu nháp (Giảng viên).
- UC5.4: Gửi yêu cầu chốt điểm LHP lên Khoa (Giảng viên).
- UC5.5: Xét duyệt điểm & Kích hoạt tính Điểm tổng môn học (Giáo vụ).
- UC5.6: Tạo yêu cầu xin sửa điểm đã chốt (Kèm lý do) (Giảng viên).
- UC5.7: Phê duyệt / Từ chối yêu cầu xin sửa điểm (Giáo vụ).

### Nhóm 6: Tổng kết Học vụ & Xếp loại
- UC6.1: Kích hoạt tính điểm GPA và xếp loại (Giáo vụ).
- UC6.2: Lọc danh sách cảnh báo học vụ (Giáo vụ).

### Nhóm 7: Tra cứu & Thống kê Báo cáo
- UC7.1: Tra cứu bảng điểm cá nhân & Tiến độ học tập (Sinh viên).
- UC7.2: Xem bảng điểm tổng hợp của LHP (Giảng viên, Giáo vụ).
- UC7.3: Xuất Bảng điểm cá nhân dạng PDF (Phiếu điểm) (Sinh viên, Giáo vụ).
- UC7.4: Xuất Danh sách Cảnh báo học vụ dạng Excel (Giáo vụ).
- UC7.5: Xuất Báo cáo thống kê tỷ lệ Xếp loại học lực toàn Khoa (Giáo vụ).

### Bước 3: Luồng xử lý
Đang cập nhật.

## Bước 4: Đặc tả Lớp Business Logic (Quy tắc Nghiệp vụ - Bản Final)

### BR1: Quy tắc Tính điểm tổng kết môn học (Từ UC5.5)
- Nguyên tắc: Điểm tổng được tính tự động bởi hệ thống ngay khi Giáo vụ nhấn "Duyệt điểm LHP", tuyệt đối không cho phép nhập tay.
- Ràng buộc toàn vẹn (Validation): Khi cấu hình LHP, hệ thống bắt buộc kiểm tra điều kiện tổng trọng số: `tyLeCC + tyLeGK + tyLeCK = 100%`. Nếu vi phạm, chặn thao tác mở lớp.
- Công thức: Điểm Tổng Hệ 10 = (Điểm CC * tyLeCC) + (Điểm GK * tyLeGK) + (Điểm CK * tyLeCK).
- Làm tròn: Làm tròn đến 1 chữ số thập phân (Ví dụ: 8.44 --> 8.4; 8.45 --> 8.5).

### BR2: Quy tắc Quy đổi Thang điểm Môn học (Hệ 10 --> Điểm Chữ --> Hệ 4)
- Nguyên tắc xử lý dữ liệu (On-the-fly): Điểm chữ và Điểm hệ 4 của từng môn học không lưu cứng vào CSDL `DS_LHP`, chỉ tính toán tức thời khi query xuất bảng điểm.
- Lưu trữ (Persistent): Điểm GPA Hệ 10 và GPA Hệ 4 tổng hợp của học kỳ/toàn khóa bắt buộc lưu vào bảng `KET_QUA_HOC_KY` để phục vụ truy xuất tốc độ cao cho BR4 và BR5.
- Bảng tham chiếu chuẩn:
    - 8.5 - 10.0 --> A (4.0)
    - 7.8 - 8.4 --> B+ (3.5)
    - 7.0 - 7.7 --> B (3.0)
    - 6.5 - 6.9 --> C+ (2.5)
    - 5.5 - 6.4 --> C (2.0)
    - 5.0 - 5.4 --> D+ (1.5)
    - 4.0 - 4.9 --> D (1.0)
    - Dưới 4.0 --> F (0.0) - Tự động đánh dấu Không đạt.

### BR3: Quy tắc Tính Điểm Trung bình chung (GPA)
- Điều kiện lọc môn: Hệ thống kiểm tra cờ `tinhGpa` (BOOLEAN) trong bảng `MONHOC`. Chỉ đưa vào công thức các môn có cờ này là TRUE (loại trừ Giáo dục Thể chất, GD Quốc phòng, v.v.).

- Quy tắc học lại / học cải thiện: Bảng điểm chi tiết vẫn lưu đầy đủ lịch sử các lần học. Khi tính GPA tích lũy toàn khóa, hệ thống chỉ lấy điểm của lần học cuối cùng (thay thế hoàn toàn điểm môn F cũ, không cộng dồn tín chỉ).

### BR4: Quy tắc Xếp loại Học lực (Từ UC6.1)
- Nguyên tắc: Dựa trên GPA tích lũy hệ 4 và kiểm tra lịch sử điểm thi.
- Ngưỡng quy định chính:
    - 3.60 - 4.00: Xuất sắc
    - 3.20 - 3.59: Giỏi
    - 2.50 - 3.19: Khá
    - 2.00 - 2.49: Trung bình
    - 1.00 - 1.99: Yếu
    - Dưới 1.00: Kém
- Điều kiện phụ (Giáng cấp xếp loại): Sinh viên thuộc nhóm Xuất sắc hoặc Giỏi sẽ bị hạ xuống một bậc nếu hệ thống truy vấn thấy trong toàn bộ lịch sử học tập có bất kỳ môn nào bị điểm F (kể cả khi đã học lại và thi qua).

### BR5: Quy tắc Xét Cảnh báo học vụ (Từ UC6.2)
- Nguyên tắc: Hệ thống tự động quét dữ liệu sau khi học kỳ kết thúc và áp dụng ngưỡng tín chỉ lũy tiến theo tiến độ khóa học.
- Ngưỡng cảnh báo mức 1: Kích hoạt nếu sinh viên rơi vào một trong các trường hợp sau:
    - GPA tích lũy < 1.20 (đối với sinh viên sau Học kỳ 1).
    - GPA tích lũy < 1.40 (đối với sinh viên sau Học kỳ 2).
    - GPA tích lũy < 1.60 (đối với sinh viên từ Học kỳ 3 trở đi).
- Ngưỡng cảnh báo mức 2 & Buộc thôi học:
    - Bị cảnh báo mức 2 nếu vi phạm ngưỡng mức 1 trong 2 học kỳ liên tiếp.
    - Đưa vào danh sách Buộc thôi học nếu bị cảnh báo mức 2 hai lần liên tiếp, hoặc bị cảnh báo học vụ tổng cộng 3 lần trong toàn khóa học.

## Ghi chú bàn giao cho backend (quan trọng trước khi code)

1) Them sinh vien vao LHP bat buoc trong 1 transaction (2 INSERT)
    - INSERT ds_lhp -> INSERT lich_su_hoc_mon
    - Neu 1 trong 2 thao tac fail thi rollback ca hai.

2) Moi thay doi diem bat buoc ghi audit trong cung transaction
    - UPDATE ds_lhp -> INSERT audit_diem
    - Khong duoc ghi audit sau khi commit rieng.

3) Hai query on-the-fly bat buoc viet thanh ham dung chung
    - co_mon_f(sinh_vien_id) (dung cho BR4)
    - dem_hoc_ky_da_qua(sinh_vien_id) (dung cho BR5)
    - Khong tu phat viet lai query o tung noi vi de sai logic.

## Chức năng chính
Thêm / sửa / xóa sinh viên
Nhập điểm, tính GPA
Xếp loại học lực
Tìm kiếm sinh viên
Xuất báo cáo

## Cách chạy dự án
(Hướng dẫn các bước cài đặt và chạy code tại đây)




# Hướng dẫn làm việc

## Quy tắc thư mục
-   assets/css: CSS nền + components (Background.css, components.css)
-   assets/js: JS dùng chung
-   assets/img: Hình ảnh
-   config: Cấu hình PHP/Python (database.php, db_config.py, constants.php)
-   src/python: Backend Python (core, models, services, api, main.py)
-   src/views: Giao diện PHP (layouts, auth, admin, giao_vu, giang_vien, sinh_vien, index.php)
-   api: Endpoint PHP trả JSON (diem.php, gpa.php, sinh_vien.php)
-   scripts: Script chạy độc lập (seed_data.py)
-   docs: Tài liệu hướng dẫn, sơ đồ lớp, file phân tích
-   data: Dữ liệu (json, csv, sql...)

## Quy trình làm việc với Git

-   Mỗi người tạo 1 nhánh riêng trên GitHub 
-   Làm việc, commit trên nhánh của mình; giữ commit nhỏ, rõ ràng.
-   Trước khi mở PR: tự kiểm tra, đảm bảo không sửa ngoài phạm vi chức năng.
-   Mở Pull Request lên `main`, mô tả ngắn gọn thay đổi, chờ review trước khi merge.

## Quy tắc đặt tên commit

-   Theo chuẩn Conventional Commits: `<type>(scope?): <short-desc>`
-   `type`: feat, fix, refactor, chore, docs, test, style, perf.
-   `scope` (tùy chọn): module hoặc khu vực ảnh hưởng, viết ngắn gọn, không dấu cách.
-   `short-desc`: mô tả ngắn gọn, tiếng Anh/Việt không dấu, dạng mệnh lệnh.
-   Ví dụ: `feat(models): add user uuid v7`; `fix(assets): correct css path`; `chore: update deps`.
-   Giải thích type:
    -   `feat`: thêm chức năng mới.
    -   `fix`: sửa lỗi.
    -   `refactor`: cải tổ mã, không đổi hành vi.
    -   `chore`: việc hỗ trợ (build, config, deps).
    -   `docs`: tài liệu.
    -   `test`: thêm/cập nhật test.
    -   `style`: định dạng, không đổi logic.
    -   `perf`: tối ưu hiệu năng.

## Quy tắc đặt tên trong cơ sở dữ liệu

-   Dùng `snake_case` (chữ thường, từ phân tách bằng `_`) cho tất cả tên bảng và cột.
-   Khóa chính: `<entity>_id`. Ví du `user_id` và là chuẩn uuidv7 (VARCHAR 32)
-   Tên biến trong truy vấn/ORM: bám theo tên cột (`snake_case`) để đồng nhất.

## Trang truy cập PHPmyAdmin
Link: 
-   Username: 
-   Password: 
