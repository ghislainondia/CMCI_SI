-- ChurchCRM 7.3.2
-- Add per-user primary group reference

ALTER TABLE `user_usr`
  ADD COLUMN `group_id` mediumint(8) unsigned DEFAULT NULL AFTER `usr_apiKey`;
