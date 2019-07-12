INSERT INTO `users` (`username`, `password`) VALUES
('user1', '24c9e15e52afc47c225b757e7bee1f9d'),
('user2', '7e58d63b60197ceb55a1c487989a3720'),
('user3', '92877af70a45fd6a2ed7fe81e1236b78');


INSERT INTO `todos` (`id`, `user_id`, `description`, `status`) VALUES
(1, 1, 'Vivamus tempus', 0),
(2, 1, 'lorem ac odio', 0),
(3, 1, 'Ut congue odio', 0),
(5, 1, 'Accumsan nunc vitae', 1),
(6, 2, 'Lorem ipsum', 0),
(7, 2, 'In lacinia est', 0),
(8, 2, 'Odio varius gravida', 0),
(37, 1, ' printing and typesetting industry', 0),
(38, 1, 'simply dummy text', 0),
(39, 1, 'simply dummy text 2', 0);
