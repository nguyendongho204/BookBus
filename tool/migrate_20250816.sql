-- Thêm trạng thái: 1 = active, 0 = locked
ALTER TABLE daily_dangky ADD COLUMN status INTEGER NOT NULL DEFAULT 1;

-- Xóa mềm: null = chưa xóa, khác null = thời điểm xóa
ALTER TABLE daily_dangky ADD COLUMN deleted_at TEXT NULL;
