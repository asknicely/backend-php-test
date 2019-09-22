INSERT INTO users (username, password) VALUES
('user1', '$2y$10$7PS5Z0VrFTrj/qWfB7zLI.XGhwwYurzTyjQObdW9ZltaMn4z04zpK'),
('user2', '$2y$10$cX9q6c/iJ3b/sbbhfMqRIunwkay8EJvO94qBnQOR06606wEam5i8K'),
('user3', '$2y$10$eBxqTEglX/xOhwBOt1zD9eSniom8fUF92oU8WtQrEebQ0FrIoAORm');

INSERT INTO todos (user_id, description) VALUES
(1, 'Vivamus tempus'),
(1, 'lorem ac odio'),
(1, 'Ut congue odio'),
(1, 'Sodales finibus'),
(1, 'Accumsan nunc vitae'),
(2, 'Lorem ipsum'),
(2, 'In lacinia est'),
(2, 'Odio varius gravida');
