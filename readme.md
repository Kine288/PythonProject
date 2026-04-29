# Dự án Quản lý Sinh viên
Dự án giúp quản lý thông tin sinh viên, điểm số và các lớp học.

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
-   src: Mã nguồn chính 
-   docs: Tài liệu hướn dẫn, sơ đồ lớp, file phân tích
-   data: chứa các file dl (json, csv, sql...)
-   Tài nguyên tĩnh (CSS/JS) đặt trong `assets`.

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
