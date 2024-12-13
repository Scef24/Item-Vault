CREATE TABLE admin (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  email varchar(100) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE INDEX email (email),
  UNIQUE INDEX username (username)
)
ENGINE = INNODB
AUTO_INCREMENT = 3
AVG_ROW_LENGTH = 16384
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci
ROW_FORMAT = DYNAMIC;

CREATE TABLE item_categories (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX name (name)
)
ENGINE = INNODB
AUTO_INCREMENT = 8
AVG_ROW_LENGTH = 2340
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci
ROW_FORMAT = DYNAMIC;

CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  first_name varchar(50) NOT NULL,
  middle_name varchar(50) DEFAULT NULL,
  last_name varchar(50) NOT NULL,
  student_id varchar(20) NOT NULL,
  department varchar(100) NOT NULL,
  year varchar(20) NOT NULL,
  section varchar(20) NOT NULL,
  email varchar(100) NOT NULL,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE INDEX email (email),
  UNIQUE INDEX student_id (student_id),
  UNIQUE INDEX username (username)
)
ENGINE = INNODB
AUTO_INCREMENT = 5
AVG_ROW_LENGTH = 4096
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci
ROW_FORMAT = DYNAMIC;

CREATE TABLE items (
  id int(11) NOT NULL AUTO_INCREMENT,
  type enum ('lost', 'found') NOT NULL,
  title varchar(100) NOT NULL,
  description text DEFAULT NULL,
  location varchar(100) DEFAULT NULL,
  date_posted timestamp DEFAULT CURRENT_TIMESTAMP,
  date_item datetime NOT NULL,
  status enum ('pending', 'processing', 'approved', 'claimed', 'returned') DEFAULT 'pending',
  image_path varchar(255) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  category_id int(11) DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  location_found varchar(255) DEFAULT NULL,
  date_found date DEFAULT NULL,
  item_description text DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX category_id (category_id),
  CONSTRAINT fk_user_items FOREIGN KEY (user_id)
  REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT items_ibfk_1 FOREIGN KEY (user_id)
  REFERENCES users (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT items_ibfk_2 FOREIGN KEY (category_id)
  REFERENCES item_categories (id) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 13
AVG_ROW_LENGTH = 2730
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci
ROW_FORMAT = DYNAMIC;

CREATE TABLE claims (
  id int(11) NOT NULL AUTO_INCREMENT,
  item_id int(11) DEFAULT NULL,
  claimer_id int(11) DEFAULT NULL,
  claim_date timestamp DEFAULT CURRENT_TIMESTAMP,
  status enum ('pending', 'approved', 'rejected') DEFAULT 'pending',
  PRIMARY KEY (id),
  INDEX item_id (item_id),
  CONSTRAINT claims_ibfk_1 FOREIGN KEY (item_id)
  REFERENCES items (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT claims_ibfk_2 FOREIGN KEY (claimer_id)
  REFERENCES users (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_user_claims FOREIGN KEY (claimer_id)
  REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 10
AVG_ROW_LENGTH = 2730
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci
ROW_FORMAT = DYNAMIC;
