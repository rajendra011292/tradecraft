CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email_verified_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(128) NOT NULL UNIQUE,
  type ENUM('verify','reset') NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  symbol VARCHAR(20) NOT NULL,
  side ENUM('long','short') NOT NULL,
  entry_price DECIMAL(15,2) NOT NULL,
  stop_loss DECIMAL(15,2) NOT NULL,
  target_price DECIMAL(15,2) NOT NULL,
  notes TEXT NULL,
  status ENUM('planned','executed','canceled') NOT NULL DEFAULT 'planned',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_plans_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS plan_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id INT NOT NULL,
  user_id INT NOT NULL,
  action VARCHAR(30) NOT NULL,
  reason VARCHAR(255) NULL,
  snapshot JSON NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_events_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
  CONSTRAINT fk_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add this before the position_events table
CREATE TABLE IF NOT EXISTS trade_positions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id INT NULL,
  user_id INT NOT NULL,
  symbol VARCHAR(20) NOT NULL,
  side ENUM('long','short') NOT NULL,
  entry_price DECIMAL(15,2) NOT NULL,
  stop_loss DECIMAL(15,2) NOT NULL,
  target_price DECIMAL(15,2) NOT NULL,
  qty DECIMAL(15,8) NOT NULL,
  capital_used DECIMAL(15,2) NOT NULL,
  rr_at_entry DECIMAL(10,2) NOT NULL,
  est_profit_at_entry DECIMAL(15,2) NOT NULL,
  status ENUM('ongoing','completed') NOT NULL DEFAULT 'ongoing',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  executed_at DATETIME NOT NULL,
  completed_at DATETIME NULL,
  trailing_enabled TINYINT(1) NOT NULL DEFAULT 0,
  trailing_type ENUM('fixed','percent','ATR') NULL,
  trailing_value DECIMAL(15,4) NULL,
  breakeven_enabled TINYINT(1) NOT NULL DEFAULT 0,
  realized_pl_total DECIMAL(15,2) NULL,
  avg_exit_price DECIMAL(15,2) NULL,
  CONSTRAINT fk_positions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_positions_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Then add the position_events table
CREATE TABLE IF NOT EXISTS position_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  position_id INT NOT NULL,
  user_id INT NOT NULL,
  action VARCHAR(30) NOT NULL,
  reason VARCHAR(255) NULL,
  snapshot JSON NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_pe_position FOREIGN KEY (position_id) REFERENCES trade_positions(id) ON DELETE CASCADE,
  CONSTRAINT fk_pe_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;