CREATE TABLE IF NOT EXISTS audit_log (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id      INTEGER,
  username     TEXT,
  action       TEXT,
  object_type  TEXT,
  object_id    TEXT,
  details_json TEXT,
  ip_address   TEXT,
  user_agent   TEXT,
  created_at   TEXT DEFAULT (datetime('now','localtime'))
);

CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_log(created_at);
CREATE INDEX IF NOT EXISTS idx_audit_action  ON audit_log(action);
CREATE INDEX IF NOT EXISTS idx_audit_object  ON audit_log(object_type, object_id);
