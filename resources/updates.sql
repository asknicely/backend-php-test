alter table todos
	add status enum('New', 'Completed') default 'new' not null;