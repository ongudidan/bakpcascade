CREATE TABLE IF NOT EXISTS `#__imageshow_theme_grid` (
  `theme_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `img_layout` char(5) DEFAULT 'fixed',
  `background_color` char(30) DEFAULT '#ffffff',
  `thumbnail_width` int(11) DEFAULT '50',
  `thumbnail_height` int(11) DEFAULT '50',
  `thumbnail_space` int(11) DEFAULT '10',
  `thumbnail_border` int(11) DEFAULT '3',
  `thumbnail_rounded_corner` int(11) DEFAULT '3',
  `thumbnail_border_color` char(30) DEFAULT '#ffffff',
  `thumbnail_shadow` char(1) DEFAULT '1',
  PRIMARY KEY (`theme_id`)
) DEFAULT CHARSET=utf8;