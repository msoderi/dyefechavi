-- dyefechavi database schema
-- --------------------------

-- admin_channels table contains channels from which displayed news are taken.
-- it is populated by an ad-hoc external engine (synd engine in the follow), available at github as the dyefechase project
-- id is the autoinc primary key
-- channel is the channel url
-- referer is the url of the webpage where the link to the channel was found
-- lastmodified is the last modified datetime declared by channel owners at the time of the last parsing of the channel by synd engine
-- contentlength is the content length declared by channel owners at the time of the last parsing of the channel by synd engine
-- skip if true means that news from that channel are not taken by synd engine

CREATE TABLE `admin__channels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `channel` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `LastModified` varchar(255) NOT NULL,
  `ContentLength` varchar(255) NOT NULL,
  `skip` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel` (`channel`)
) ENGINE=InnoDB AUTO_INCREMENT=406415 DEFAULT CHARSET=utf8 AUTO_INCREMENT=406415 ;

-- admin_news is the table that contains the news displayed to the users
-- it is populated by an ad-hoc external engine available at github as dyefechapa project
-- autoinc is the autoinc primary key
-- id is the business key, a unique identifier for the news
-- channel is the url of the channel where the news was found
-- content is the actual news in a particular well defined xml format. A sample news content follows:
--
-- <?xml version="1.0" encoding="utf-8"?>
-- <synd>
-- 	<id>c056f91d516b28b49a8785576f556665@http://www.comune.morozzo.cn.it/taxonomy/term/14/all/feed</id>
--	<type>news</type>
--	<datetime>1416610789</datetime>
--	<channelurl>http://www.comune.morozzo.cn.it/taxonomy/term/14/all/feed</channelurl>
--	<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"
--  xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:og="http://ogp.me/ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
--  xmlns:sioc="http://rdfs.org/sioc/ns#" xmlns:sioct="http://rdfs.org/sioc/types#" xmlns:skos="http://www.w3.org/2004/02/skos/core#"
--  xmlns:xsd="http://www.w3.org/2001/XMLSchema#" version="2.0" xml:base="http://www.comune.morozzo.cn.it/taxonomy/term/14/all">
--  <channel>
--    <title>Ufficio tecnico</title>
--    <link>http://www.comune.morozzo.cn.it/taxonomy/term/14/all</link>
--    <description/>
--    <language>it</language>
--          <item>
--    <title>635546308</title>
--    <link>http://www.comune.morozzo.cn.it/node/753</link>
--    <description>actual news content</description>
--     <pubDate>Wed, 26 Jun 2013 13:35:10 +0000</pubDate>
-- <dc:creator>Comune</dc:creator>
-- <guid isPermaLink="false">753 at http://www.comune.morozzo.cn.it</guid>
--  </item>
--  </channel>
-- </rss>
-- </synd>
--
-- where rss tag replicates rss tag of original channel stripping all news but the one to which the table row is related to
-- skip if true means that the news must be hidden to users

CREATE TABLE `admin__news` (
  `autoinc` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` varchar(255) NOT NULL,
  `channel` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `skip` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`autoinc`),
  UNIQUE KEY `id` (`id`),
  KEY `channel` (`channel`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM AUTO_INCREMENT=855097 DEFAULT CHARSET=utf8 AUTO_INCREMENT=855097 ;

-- admin_query_sintax_tips contains tips for helping users to have the best possible experience
-- id is the autoinc primary key
-- intro is a short intro to the tip
-- descr is a long description of the feature

CREATE TABLE `admin__query_sintax_tips` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `intro` text NOT NULL,
  `descr` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- favorite_news keeps track of bookmarked news
-- id is the autoinc primary key
-- user is the user that bookmarked the news
-- newsid is the logical identifier of the bookmarked news (refer to admin_news for explanation)
-- newstitle is the title of the bookmarked news

CREATE TABLE `favorite__news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `newsid` varchar(255) NOT NULL,
  `newstitle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- favorite_channel keeps track of bookmarked channels
-- id is the autoinc primary key
-- user is the user that bookmarked the channel
-- channel is the url of the bookmarked channel

CREATE TABLE `favorite__channel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `channel` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- stats_visualizzazioni keeps track of each news read
-- id is the autoinc primary key
-- ip is the ip from which the request for the news web page arrived
-- sessid is the sessionid transmitted by the user when requesting for the news web page
-- datetime is the date and time when the request arrived
-- newsid is the logical id of the requested news (check admin_news for explanation)
-- newstitle is the title of the requested news

CREATE TABLE `stats__visualizzazioni` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) NOT NULL,
  `sessid` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL,
  `newsid` varchar(255) NOT NULL,
  `newstitle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- stats_referer keeps track of users provenance
-- id is the autoinc primary key
-- ip is the ip from which the request for the news web page arrived
-- sessid is the sessionid transmitted by the user when requesting for the news web page
-- datetime is the date and time when the request was issued
-- referer is the url of the web page from which the user arrived 
-- newsid is the logical id of the news where the user landed (see admin_news for logical id)

CREATE TABLE `stats__referer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) NOT NULL,
  `sessid` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL,
  `referer` varchar(255) NOT NULL,
  `newsid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8 AUTO_INCREMENT=83 ;

-- twitter_shorturl is used for composing short urls to synd news when publishing to twitter  
-- twitter shorturls referring to synd news are something like synd.it/111111 where 111111 is mapped to a synd news by means of this twitter_shorturl table
-- id is a small number, included in shorturl for brefly referring to a synd news
-- news is the logic identifier for the referred news (see admin_news for short identifier)

CREATE TABLE `twitter__shorturl` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `news` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `news` (`news`)
) ENGINE=InnoDB AUTO_INCREMENT=7265703 DEFAULT CHARSET=utf8 AUTO_INCREMENT=7265703 ;