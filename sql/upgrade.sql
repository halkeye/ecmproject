alter table verificationcodes add `type` INT UNSIGNED NOT NULL AFTER `account_id`;
alter table verificationcodes add `value` VARCHAR(255) NOT NULL AFTER `code`;
alter table verificationcodes drop key `account_id`;
