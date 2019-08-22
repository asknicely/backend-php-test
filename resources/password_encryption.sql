ALTER table `users` MODIFY `password` CHAR(64) NOT NULL;

UPDATE `users` SET `password` = '0a041b9462caa4a31bac3567e0b6e6fd9100787db2ab433d96f6d178cabfce90' WHERE `id` = 1;

UPDATE `users` SET `password` = '6025d18fe48abd45168528f18a82e265dd98d421a7084aa09f61b341703901a3' WHERE `id` = 2;

UPDATE `users` SET `password` = '5860faf02b6bc6222ba5aca523560f0e364ccd8b67bee486fe8bf7c01d492ccb' WHERE `id` = 3;