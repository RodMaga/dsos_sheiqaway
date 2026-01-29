-- Hotel Insert Stored Procedure
DROP PROCEDURE IF EXISTS sp_hotel_insert;

DELIMITER $$

CREATE PROCEDURE sp_hotel_insert(
    IN p_name VARCHAR(150),
    IN p_description TEXT,
    IN p_address VARCHAR(255),
    IN p_phone VARCHAR(20)
)
BEGIN
    INSERT INTO hotels (name, description, address, phone, hotel_status_id, average_rating, created_at, updated_at)
    VALUES (p_name, p_description, p_address, p_phone, 1, 0, NOW(), NOW());
    
    SELECT LAST_INSERT_ID() as id;
END$$

DELIMITER ;

-- Hotel Get By ID with Average Rating
DROP PROCEDURE IF EXISTS sp_hotel_get_by_id;

DELIMITER $$

CREATE PROCEDURE sp_hotel_get_by_id(
    IN p_id INT
)
BEGIN
    SELECT 
        h.*,
        COALESCE(AVG(hr.rating), 0) as average_rating
    FROM hotels h
    LEFT JOIN hotel_ratings hr ON h.id = hr.hotel_id
    WHERE h.id = p_id
    GROUP BY h.id;
END$$

DELIMITER ;

-- Hotel Get All with Average Rating
DROP PROCEDURE IF EXISTS sp_hotel_get_all;

DELIMITER $$

CREATE PROCEDURE sp_hotel_get_all()
BEGIN
    SELECT 
        h.*,
        COALESCE(AVG(hr.rating), 0) as average_rating
    FROM hotels h
    LEFT JOIN hotel_ratings hr ON h.id = hr.hotel_id
    GROUP BY h.id;
END$$

DELIMITER ;

-- Bedroom Insert Stored Procedure
DROP PROCEDURE IF EXISTS sp_bedroom_insert;

DELIMITER $$

CREATE PROCEDURE sp_bedroom_insert(
    IN p_name VARCHAR(100),
    IN p_description TEXT
)
BEGIN
    INSERT INTO bedrooms (name, description, hotel_bedroom_status_id, created_at, updated_at)
    VALUES (p_name, p_description, 1, NOW(), NOW());
    
    SELECT LAST_INSERT_ID() as id;
END$$

DELIMITER ;

-- Reservation Insert Stored Procedure
DROP PROCEDURE IF EXISTS sp_reservation_insert;

DELIMITER $$

CREATE PROCEDURE sp_reservation_insert(
    IN p_bedroom_id INT,
    IN p_user_id INT,
    IN p_hotel_id INT,
    IN p_quantity INT,
    IN p_check_in DATETIME,
    IN p_check_out DATETIME,
    IN p_price DECIMAL(10, 2)
)
BEGIN
    DECLARE v_booking_reference VARCHAR(20);
    
    -- Generate booking reference
    SET v_booking_reference = CONCAT('SHQ-', UPPER(SUBSTR(UUID(), 1, 6)));
    
    INSERT INTO reservations (bedroom_id, user_id, hotel_id, quantity, check_in, check_out, price, reservation_status_id, booking_reference, created_at, updated_at)
    VALUES (p_bedroom_id, p_user_id, p_hotel_id, p_quantity, p_check_in, p_check_out, p_price, 1, v_booking_reference, NOW(), NOW());
    
    SELECT LAST_INSERT_ID() as id;
END$$

DELIMITER ;

-- User Insert Stored Procedure
DROP PROCEDURE IF EXISTS sp_user_insert;

DELIMITER $$

CREATE PROCEDURE sp_user_insert(
    IN p_name VARCHAR(100),
    IN p_email VARCHAR(150),
    IN p_phone VARCHAR(20),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO user_hotel (name, email, phone, password, is_admin, user_status_id, created_at, updated_at)
    VALUES (p_name, p_email, p_phone, p_password, 0, 1, NOW(), NOW());
    
    SELECT LAST_INSERT_ID() as id;
END$$

DELIMITER ;

-- Hotel Rating Insert Stored Procedure
DROP PROCEDURE IF EXISTS sp_hotel_rating_insert;

DELIMITER $$

CREATE PROCEDURE sp_hotel_rating_insert(
    IN p_hotel_id INT,
    IN p_user_id INT,
    IN p_rating TINYINT
)
BEGIN
    INSERT INTO hotel_ratings (hotel_id, user_id, rating, created_at, updated_at)
    VALUES (p_hotel_id, p_user_id, p_rating, NOW(), NOW());
    
    SELECT LAST_INSERT_ID() as id;
END$$

DELIMITER ;

-- Hotel Update Stored Procedure
DROP PROCEDURE IF EXISTS sp_hotel_update;

DELIMITER $$

CREATE PROCEDURE sp_hotel_update(
    IN p_id INT,
    IN p_name VARCHAR(150),
    IN p_description TEXT,
    IN p_address VARCHAR(255),
    IN p_phone VARCHAR(20),
    IN p_status_id INT
)
BEGIN
    UPDATE hotels 
    SET name = p_name,
        description = p_description,
        address = p_address,
        phone = p_phone,
        hotel_status_id = p_status_id,
        updated_at = NOW()
    WHERE id = p_id;
    
    SELECT 
        h.*,
        COALESCE(AVG(hr.rating), 0) as average_rating
    FROM hotels h
    LEFT JOIN hotel_ratings hr ON h.id = hr.hotel_id
    WHERE h.id = p_id
    GROUP BY h.id;
END$$

DELIMITER ;

-- Bedroom Update Stored Procedure
DROP PROCEDURE IF EXISTS sp_bedroom_update;

DELIMITER $$

CREATE PROCEDURE sp_bedroom_update(
    IN p_id INT,
    IN p_name VARCHAR(100),
    IN p_description TEXT,
    IN p_status_id INT
)
BEGIN
    UPDATE bedrooms 
    SET name = p_name,
        description = p_description,
        hotel_bedroom_status_id = p_status_id,
        updated_at = NOW()
    WHERE id = p_id;
    
    SELECT * FROM bedrooms WHERE id = p_id;
END$$

DELIMITER ;

-- Reservation Update Stored Procedure
DROP PROCEDURE IF EXISTS sp_reservation_update;

DELIMITER $$

CREATE PROCEDURE sp_reservation_update(
    IN p_id INT,
    IN p_bedroom_id INT,
    IN p_quantity INT,
    IN p_check_in DATETIME,
    IN p_check_out DATETIME,
    IN p_price DECIMAL(10, 2)
)
BEGIN
    UPDATE reservations 
    SET bedroom_id = p_bedroom_id,
        quantity = p_quantity,
        check_in = p_check_in,
        check_out = p_check_out,
        price = p_price,
        updated_at = NOW()
    WHERE id = p_id;
    
    SELECT * FROM reservations WHERE id = p_id;
END$$

DELIMITER ;

-- User Update Stored Procedure
DROP PROCEDURE IF EXISTS sp_user_update;

DELIMITER $$

CREATE PROCEDURE sp_user_update(
    IN p_id INT,
    IN p_name VARCHAR(100),
    IN p_email VARCHAR(150),
    IN p_phone VARCHAR(20),
    IN p_password VARCHAR(255)
)
BEGIN
    UPDATE user_hotel 
    SET name = p_name,
        email = p_email,
        phone = p_phone,
        password = p_password,
        updated_at = NOW()
    WHERE id = p_id;
    
    SELECT * FROM user_hotel WHERE id = p_id;
END$$

DELIMITER ;
