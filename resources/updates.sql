alter table todos
	add status enum('New', 'Completed') default 'new' not null;
update users set password = '$2y$10$lolah5XvDvuq0ozfJgOccuCIH3b1zxBLXotnViHABRjd/2z3SSxZW' where id = 1;
update users set password = '$2y$10$WhXHxv80D/byYfmDu6GYKu0vpKL0Pgjf2pqpPW.54/.Fu8jcEg5Ym' where id = 2;
update users set password = '$2y$10$I8jSSAN5k9PRbv03qm5yEuJfubNitL9KJqNV3Z9TcGGSIFXfV/i92' where id = 3;