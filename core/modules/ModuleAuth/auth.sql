CREATE TABLE `users` (
  `id`    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `login` VARCHAR(50)  NOT NULL UNIQUE,
  `pass`  VARCHAR(255) NOT NULL
);

CREATE TABLE `users_tokens` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `token`      VARCHAR(255) NOT NULL UNIQUE,
  `user_ip`    VARCHAR(255) NOT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `expires`    BIGINT UNSIGNED,
  `created`    BIGINT UNSIGNED,
  `user_agent` VARCHAR(255) NOT NULL,
  CONSTRAINT `users_tokens_fk_users`
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE RESTRICT
);
# roles

CREATE TABLE `roles` (
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE
);
 CREATE TABLE `user_roles`(
   `user_id` INT UNSIGNED NOT NULL ,
   `role_id` INT UNSIGNED NOT NULL,
   CONSTRAINT `user_role_fk_users`
   FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
     ON DELETE CASCADE
     ON UPDATE RESTRICT,
   CONSTRAINT `user_role_fk_users`
   FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
     ON DELETE CASCADE
     ON UPDATE RESTRICT,
   PRIMARY KEY (`user_id`,`role_id`)
 );