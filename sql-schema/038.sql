ALTER TABLE  `observium`.`munin_plugins` DROP INDEX  `UNIQUE` ,ADD UNIQUE  `dev_mplug` (  `device_id` ,  `mplug_type` );
ALTER TABLE  `observium`.`applications` DROP INDEX  `unique` ,ADD UNIQUE  `dev_type_inst` (  `device_id` ,  `app_type` ,  `app_instance` );
