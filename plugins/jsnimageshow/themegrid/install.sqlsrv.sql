SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__imageshow_theme_grid]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__imageshow_theme_grid](
  [theme_id] [int] IDENTITY(1,1) NOT NULL,  
  [img_layout] [nvarchar](50) DEFAULT 'fixed',
  [background_color] [nvarchar](50) DEFAULT '#ffffff',
  [thumbnail_width] [nvarchar](50) DEFAULT '50',
  [thumbnail_height] [nvarchar](30) DEFAULT '50',
  [thumbnail_space] [nvarchar](50) DEFAULT '10',
  [thumbnail_border] [nvarchar](50) DEFAULT '3',
  [thumbnail_rounded_corner] [nvarchar](50) DEFAULT '3',
  [thumbnail_border_color] [nvarchar](50) DEFAULT '#ffffff',
  [thumbnail_shadow] [nvarchar](50) DEFAULT '1',
 CONSTRAINT [PK_#__imageshow_theme_grid_theme_id] PRIMARY KEY CLUSTERED 
(
	[theme_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;