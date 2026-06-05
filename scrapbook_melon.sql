-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2026 at 10:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `scrapbook_melon`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_cache`
--

CREATE TABLE `app_cache` (
  `cache_key` varchar(191) NOT NULL,
  `cache_value` longtext NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_cache`
--

INSERT INTO `app_cache` (`cache_key`, `cache_value`, `expires_at`, `updated_at`) VALUES
('spotify_app_token', 'BQClQbLj3LIAOCxmju73GvzeOotkf1zOru4P0wbQlYqB07N8ExxFbN5RcCQhkYimJPCDoMk900PlkAvXSb-5tsIY5v2ZfKTu-nnOiNl9FyfeLZP0rl5D3riNtzco1OwZ3_opZij55hE', 1780075164, '2026-05-29 16:20:24'),
('yt_it_1f4af0d967b738cb84c285c97c470357', '{\"responseContext\":{\"visitorData\":\"CgtnbWtFdDhzbWNDVSjI1-jQBjIoCgJFUxIiEh4SHAsMDg8QERITFBUWFxgZGhscHR4fICEiIyQlJicgIw%3D%3D\",\"serviceTrackingParams\":[{\"service\":\"CSI\",\"params\":[{\"key\":\"c\",\"value\":\"WEB\"},{\"key\":\"cver\",\"value\":\"2.20240101.05.00\"},{\"key\":\"yt_li\",\"value\":\"0\"},{\"key\":\"ResolveUrl_rid\",\"value\":\"0x21f70a52930afee1\"}]},{\"service\":\"GFEEDBACK\",\"params\":[{\"key\":\"logged_in\",\"value\":\"0\"},{\"key\":\"visitor_data\",\"value\":\"CgtnbWtFdDhzbWNDVSjI1-jQBjIoCgJFUxIiEh4SHAsMDg8QERITFBUWFxgZGhscHR4fICEiIyQlJicgIw%3D%3D\"}]},{\"service\":\"GUIDED_HELP\",\"params\":[{\"key\":\"logged_in\",\"value\":\"0\"}]},{\"service\":\"ECATCHER\",\"params\":[{\"key\":\"client.version\",\"value\":\"2.20250331\"},{\"key\":\"client.name\",\"value\":\"WEB\"}]}],\"mainAppWebResponseContext\":{\"loggedOut\":true},\"responseId\":\"IhMIjunqtN3flAMVGHdBAh3y8CY6\",\"webResponseContextExtensionData\":{\"hasDecorated\":true}},\"endpoint\":{\"clickTrackingParams\":\"IhMIjunqtN3flAMVGHdBAh3y8CY6MghleHRlcm5hbMoBBF4dOKU=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/youtubei/v1/navigation/resolve_url\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"},\"resolveUrlCommandMetadata\":{\"isVanityUrl\":true}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EgC4AQCSAwDyBgQKAjIA\"}}}', 1780103640, '2026-05-30 00:14:00');
INSERT INTO `app_cache` (`cache_key`, `cache_value`, `expires_at`, `updated_at`) VALUES
('yt_it_59c086c78e6bbd3475598d29a6989b3e', '{\"responseContext\":{\"visitorData\":\"CgtFTVlFWVRkUncxbyjJ1-jQBjIoCgJFUxIiEh4SHAsMDg8QERITFBUWFxgZGhscHR4fICEiIyQlJicgQQ%3D%3D\",\"serviceTrackingParams\":[{\"service\":\"GFEEDBACK\",\"params\":[{\"key\":\"route\",\"value\":\"channel.playlists\"},{\"key\":\"is_owner\",\"value\":\"false\"},{\"key\":\"is_alc_surface\",\"value\":\"false\"},{\"key\":\"browse_id\",\"value\":\"UCCWfUXfPgp3DEBpPem_LQOw\"},{\"key\":\"browse_id_prefix\",\"value\":\"\"},{\"key\":\"logged_in\",\"value\":\"0\"},{\"key\":\"visitor_data\",\"value\":\"CgtFTVlFWVRkUncxbyjJ1-jQBjIoCgJFUxIiEh4SHAsMDg8QERITFBUWFxgZGhscHR4fICEiIyQlJicgQQ%3D%3D\"}]},{\"service\":\"GOOGLE_HELP\",\"params\":[{\"key\":\"browse_id\",\"value\":\"UCCWfUXfPgp3DEBpPem_LQOw\"},{\"key\":\"browse_id_prefix\",\"value\":\"\"}]},{\"service\":\"CSI\",\"params\":[{\"key\":\"c\",\"value\":\"WEB\"},{\"key\":\"cver\",\"value\":\"2.20240101.05.00\"},{\"key\":\"yt_li\",\"value\":\"0\"},{\"key\":\"GetChannelPage_rid\",\"value\":\"0x126f070fae0cef69\"}]},{\"service\":\"GUIDED_HELP\",\"params\":[{\"key\":\"logged_in\",\"value\":\"0\"}]},{\"service\":\"ECATCHER\",\"params\":[{\"key\":\"client.version\",\"value\":\"2.20250331\"},{\"key\":\"client.name\",\"value\":\"WEB\"}]}],\"maxAgeSeconds\":300,\"mainAppWebResponseContext\":{\"loggedOut\":true,\"trackingParam\":\"k5_fmPxhoXZRnecYEWHVJYPfwTsquK25Kb9qLbg5--nuJ0FilUJnsHPbBwRMkusEmIBwOcCw59TLtslLKPQGSS\"},\"responseId\":\"IhMIq_P4tN3flAMVY-NJBx3uliHG\",\"webResponseContextExtensionData\":{\"webResponseContextPreloadData\":{\"preloadMessageNames\":[\"pageHeaderRenderer\",\"pageHeaderViewModel\",\"dynamicTextViewModel\",\"decoratedAvatarViewModel\",\"avatarViewModel\",\"contentMetadataViewModel\",\"flexibleActionsViewModel\",\"buttonViewModel\",\"modalWithTitleAndButtonRenderer\",\"buttonRenderer\",\"descriptionPreviewViewModel\",\"engagementPanelSectionListRenderer\",\"engagementPanelTitleHeaderRenderer\",\"sectionListRenderer\",\"itemSectionRenderer\",\"continuationItemRenderer\",\"channelMetadataRenderer\",\"twoColumnBrowseResultsRenderer\",\"tabRenderer\",\"gridRenderer\",\"lockupViewModel\",\"collectionThumbnailViewModel\",\"thumbnailViewModel\",\"thumbnailOverlayBadgeViewModel\",\"thumbnailBadgeViewModel\",\"thumbnailHoverOverlayViewModel\",\"lockupMetadataViewModel\",\"channelSubMenuRenderer\",\"sortFilterSubMenuRenderer\",\"expandableTabRenderer\",\"desktopTopbarRenderer\",\"topbarLogoRenderer\",\"fusionSearchboxRenderer\",\"dialogViewModel\",\"dialogHeaderViewModel\",\"panelFooterViewModel\",\"basicContentViewModel\",\"topbarMenuButtonRenderer\",\"multiPageMenuRenderer\",\"hotkeyDialogRenderer\",\"hotkeyDialogSectionRenderer\",\"hotkeyDialogSectionOptionRenderer\",\"microformatDataRenderer\"]},\"hasDecorated\":true}},\"contents\":{\"twoColumnBrowseResultsRenderer\":{\"tabs\":[{\"tabRenderer\":{\"endpoint\":{\"clickTrackingParams\":\"CDUQ8JMBGAUiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/featured\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EghmZWF0dXJlZPIGBAoCMgA%3D\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"title\":\"Home\",\"trackingParams\":\"CDUQ8JMBGAUiEwir8_i03d-UAxVj40kHHe6WIcY=\"}},{\"tabRenderer\":{\"endpoint\":{\"clickTrackingParams\":\"CBkQ8JMBGAYiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/playlists\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EglwbGF5bGlzdHPyBgQKAkIA\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"title\":\"Playlists\",\"selected\":true,\"content\":{\"sectionListRenderer\":{\"contents\":[{\"itemSectionRenderer\":{\"contents\":[{\"gridRenderer\":{\"items\":[{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/KwdgCGMwnLE/hqdefault.jpg?sqp=-oaymwExCOADEI4CSFryq4qpAyMIARUAAIhCGAHwAQH4Af4JgALQBYoCDAgAEAEYMiBOKHIwDw==&rs=AOn4CLAObnkPRH3N4AlFw-nC_n_rzDRIwQ\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"2 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":1450547,\"darkTheme\":1450547}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":2176076,\"darkTheme\":2176076}}},\"stackColor\":{\"lightTheme\":7044761,\"darkTheme\":7766931}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Satisfactory\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDQQ0sQMGAAiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDQQ0sQMGAAiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=KwdgCGMwnLE&list=PLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"KwdgCGMwnLE\",\"playlistId\":\"PLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzMGNqcE16YVRQM1F3b1BKajBLMEU4\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2b07600863309cb1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDQQ0sQMGAAiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=KwdgCGMwnLE&list=PLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8&pp=YAHIAQHwBAD4BACiBhUB15olE5r2liNtwj50XkIdgM2et63SBwkJtQE4KixY2Kc%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"KwdgCGMwnLE\",\"playlistId\":\"PLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE5r2liNtwj50XkIdgM2et63SBwkJtQE4KixY2Kc%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzMGNqcE16YVRQM1F3b1BKajBLMEU4\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2b07600863309cb1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CDQQ0sQMGAAiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDQQ0sQMGAAiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=KwdgCGMwnLE&list=PLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"KwdgCGMwnLE\",\"playlistId\":\"PLcsWMMB25JT30cjpMzaTP3QwoPJj0K0E8\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzMGNqcE16YVRQM1F3b1BKajBLMEU4\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2b07600863309cb1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/gc0ggzMOOiM/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLCOS874IsXaNt93JP26XcEpq-1lIg\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"6 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":1058340,\"darkTheme\":1058340}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":1785660,\"darkTheme\":1785660}}},\"stackColor\":{\"lightTheme\":7051668,\"darkTheme\":7376009}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Deadlock\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"Updated 7 days ago\"}}]},{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDMQ0sQMGAEiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDMQ0sQMGAEiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=gc0ggzMOOiM&list=PLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-&pp=0gcJCcwEOCosWNin\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"gc0ggzMOOiM\",\"playlistId\":\"PLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-\",\"params\":\"OAI%3D\",\"playerParams\":\"0gcJCcwEOCosWNin\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwU0U3ODZwTkpDb3pEUzFPbzBYU2kt\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=81cd2083330e3a23&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDMQ0sQMGAEiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=gc0ggzMOOiM&list=PLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-&pp=YAHIAQHwBAD4BACiBhUB15olE42h5dGOaCzFB7U2A9A7p_4%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"gc0ggzMOOiM\",\"playlistId\":\"PLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE42h5dGOaCzFB7U2A9A7p_4%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwU0U3ODZwTkpDb3pEUzFPbzBYU2kt\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=81cd2083330e3a23&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CDMQ0sQMGAEiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDMQ0sQMGAEiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=gc0ggzMOOiM&list=PLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"gc0ggzMOOiM\",\"playlistId\":\"PLcsWMMB25JT0SE786pNJCozDS1Oo0XSi-\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwU0U3ODZwTkpDb3pEUzFPbzBYU2kt\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=81cd2083330e3a23&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/L0QC215YF0Y/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLAiI_6UxVaZWU_2bUs9Ol8A11uU8Q\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"3 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3355443,\"darkTheme\":3355443}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":4144959,\"darkTheme\":4144959}}},\"stackColor\":{\"lightTheme\":10066329,\"darkTheme\":9211020}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Peak\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDIQ0sQMGAIiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDIQ0sQMGAIiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=L0QC215YF0Y&list=PLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"L0QC215YF0Y\",\"playlistId\":\"PLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzdlIzZ2l6NHNqV3pLaHpQTWswd1Bn\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2f4402db5e581746&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDIQ0sQMGAIiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=L0QC215YF0Y&list=PLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg&pp=YAHIAQHwBAD4BACiBhUB15olE3ryiP94ig4Io0ATLvbCUaE%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"L0QC215YF0Y\",\"playlistId\":\"PLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE3ryiP94ig4Io0ATLvbCUaE%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzdlIzZ2l6NHNqV3pLaHpQTWswd1Bn\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2f4402db5e581746&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CDIQ0sQMGAIiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDIQ0sQMGAIiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=L0QC215YF0Y&list=PLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"L0QC215YF0Y\",\"playlistId\":\"PLcsWMMB25JT3vR3giz4sjWzKhzPMk0wPg\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzdlIzZ2l6NHNqV3pLaHpQTWswd1Bn\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2f4402db5e581746&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/SS0b-7LWR08/hqdefault.jpg?sqp=-oaymwExCOADEI4CSFryq4qpAyMIARUAAIhCGAHwAQH4Af4JgALQBYoCDAgAEAEYWCBiKGUwDw==&rs=AOn4CLA-5Nbsro1uLDqvIa1cC2NdgxSoUg\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"2 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":2171942,\"darkTheme\":2171942}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":3620159,\"darkTheme\":3620159}}},\"stackColor\":{\"lightTheme\":8754073,\"darkTheme\":8029836}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Sea of Thieves\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDEQ0sQMGAMiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDEQ0sQMGAMiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=SS0b-7LWR08&list=PLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"SS0b-7LWR08\",\"playlistId\":\"PLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwaWJVVkRHdVR4TE56VmFOSi01UWth\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=492d1bfbb2d6474f&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDEQ0sQMGAMiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=SS0b-7LWR08&list=PLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka&pp=YAHIAQHwBAD4BACiBhUB15olExZSWmvXHooihv_y0mzbQXk%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"SS0b-7LWR08\",\"playlistId\":\"PLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olExZSWmvXHooihv_y0mzbQXk%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwaWJVVkRHdVR4TE56VmFOSi01UWth\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=492d1bfbb2d6474f&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CDEQ0sQMGAMiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDEQ0sQMGAMiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=SS0b-7LWR08&list=PLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"SS0b-7LWR08\",\"playlistId\":\"PLcsWMMB25JT0ibUVDGuTxLNzVaNJ-5Qka\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwaWJVVkRHdVR4TE56VmFOSi01UWth\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=492d1bfbb2d6474f&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/LWUXKlV5QZM/hqdefault.jpg?sqp=-oaymwExCOADEI4CSFryq4qpAyMIARUAAIhCGAHwAQH4AfwJgALQBYoCDAgAEAEYKSApKH8wDw==&rs=AOn4CLCOW5_jU0OY0JN_eoRlV_QThEsiUA\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"16 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":1052723,\"darkTheme\":1052723}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":1842265,\"darkTheme\":1842265}}},\"stackColor\":{\"lightTheme\":7039897,\"darkTheme\":8158363}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"helldivers 2\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDAQ0sQMGAQiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDAQ0sQMGAQiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=LWUXKlV5QZM&list=PLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"LWUXKlV5QZM\",\"playlistId\":\"PLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyWmo2RHNGMHZQWHdQVjJhdWZWZnZs\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2d65172a55794193&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDAQ0sQMGAQiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=LWUXKlV5QZM&list=PLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl&pp=YAHIAQHwBAD4BACiBhUB15olE5jTboYnWo_20WKhaaGX19g%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"LWUXKlV5QZM\",\"playlistId\":\"PLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE5jTboYnWo_20WKhaaGX19g%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyWmo2RHNGMHZQWHdQVjJhdWZWZnZs\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2d65172a55794193&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CDAQ0sQMGAQiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CDAQ0sQMGAQiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=LWUXKlV5QZM&list=PLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"LWUXKlV5QZM\",\"playlistId\":\"PLcsWMMB25JT2Zj6DsF0vPXwPV2aufVfvl\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyWmo2RHNGMHZQWHdQVjJhdWZWZnZs\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2d65172a55794193&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/G-Dj5Gp-mvQ/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLB-hxXlZt8CzBB-CRDf0I1U2R_4-A\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"1 video\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3353383,\"darkTheme\":3353383}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":4142641,\"darkTheme\":4142641}}},\"stackColor\":{\"lightTheme\":10060662,\"darkTheme\":9666934}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Deepwoken\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC8Q0sQMGAUiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT04qIWtKUm-84bRigqW5ieW\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT04qIWtKUm-84bRigqW5ieW\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT04qIWtKUm-84bRigqW5ieW\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC8Q0sQMGAUiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=G-Dj5Gp-mvQ&list=PLcsWMMB25JT04qIWtKUm-84bRigqW5ieW\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"G-Dj5Gp-mvQ\",\"playlistId\":\"PLcsWMMB25JT04qIWtKUm-84bRigqW5ieW\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwNHFJV3RLVW0tODRiUmlncVc1aWVX\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=1be0e3e46a7e9af4&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC8Q0sQMGAUiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=G-Dj5Gp-mvQ&list=PLcsWMMB25JT04qIWtKUm-84bRigqW5ieW&pp=YAHIAQHwBAD4BACiBhUB15olEzmNOQzkqYa4zSAYp-05gfo%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"G-Dj5Gp-mvQ\",\"playlistId\":\"PLcsWMMB25JT04qIWtKUm-84bRigqW5ieW\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olEzmNOQzkqYa4zSAYp-05gfo%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwNHFJV3RLVW0tODRiUmlncVc1aWVX\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=1be0e3e46a7e9af4&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CC8Q0sQMGAUiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC8Q0sQMGAUiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=G-Dj5Gp-mvQ&list=PLcsWMMB25JT04qIWtKUm-84bRigqW5ieW\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"G-Dj5Gp-mvQ\",\"playlistId\":\"PLcsWMMB25JT04qIWtKUm-84bRigqW5ieW\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwNHFJV3RLVW0tODRiUmlncVc1aWVX\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=1be0e3e46a7e9af4&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/gsfP1yZY6-E/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLBnZRcNeSklqtxuceQ1v2otvkfKwQ\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"1 video\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":2629137,\"darkTheme\":2629137}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":4995104,\"darkTheme\":4995104}}},\"stackColor\":{\"lightTheme\":12560005,\"darkTheme\":9668214}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Premios Melon\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC4Q0sQMGAYiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT21JnO_Nhwi88Lewkhnplew\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT21JnO_Nhwi88Lewkhnplew\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT21JnO_Nhwi88Lewkhnplew\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC4Q0sQMGAYiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=gsfP1yZY6-E&list=PLcsWMMB25JT21JnO_Nhwi88Lewkhnplew\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"gsfP1yZY6-E\",\"playlistId\":\"PLcsWMMB25JT21JnO_Nhwi88Lewkhnplew\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyMUpuT19OaHdpODhMZXdraG5wbGV3\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=82c7cfd72658ebe1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC4Q0sQMGAYiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=gsfP1yZY6-E&list=PLcsWMMB25JT21JnO_Nhwi88Lewkhnplew&pp=YAHIAQHwBAD4BACiBhUB15olExEPo-OfhY_sOIOGk8NjfBE%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"gsfP1yZY6-E\",\"playlistId\":\"PLcsWMMB25JT21JnO_Nhwi88Lewkhnplew\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olExEPo-OfhY_sOIOGk8NjfBE%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyMUpuT19OaHdpODhMZXdraG5wbGV3\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=82c7cfd72658ebe1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CC4Q0sQMGAYiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC4Q0sQMGAYiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=gsfP1yZY6-E&list=PLcsWMMB25JT21JnO_Nhwi88Lewkhnplew\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"gsfP1yZY6-E\",\"playlistId\":\"PLcsWMMB25JT21JnO_Nhwi88Lewkhnplew\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyMUpuT19OaHdpODhMZXdraG5wbGV3\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=82c7cfd72658ebe1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/mTd1eDpQRKM/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLBNA-M9qtnWWWqh0x8Ln6LooErLdg\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"5 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3345676,\"darkTheme\":3345676}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":5838870,\"darkTheme\":5838870}}},\"stackColor\":{\"lightTheme\":10054763,\"darkTheme\":9664374}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Minecraft\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC0Q0sQMGAciEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC0Q0sQMGAciEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=mTd1eDpQRKM&list=PLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"mTd1eDpQRKM\",\"playlistId\":\"PLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxWTBWUTFSNVp4Z0thUFRiTkwxTGFv\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=993775783a5044a3&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC0Q0sQMGAciEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=mTd1eDpQRKM&list=PLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao&pp=YAHIAQHwBAD4BACiBhUB15olE_k1ua-IEwNxXmZlmWF7r3E%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"mTd1eDpQRKM\",\"playlistId\":\"PLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE_k1ua-IEwNxXmZlmWF7r3E%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxWTBWUTFSNVp4Z0thUFRiTkwxTGFv\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=993775783a5044a3&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CC0Q0sQMGAciEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CC0Q0sQMGAciEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=mTd1eDpQRKM&list=PLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"mTd1eDpQRKM\",\"playlistId\":\"PLcsWMMB25JT1Y0VQ1R5ZxgKaPTbNL1Lao\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxWTBWUTFSNVp4Z0thUFRiTkwxTGFv\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=993775783a5044a3&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/tnCKxklSuVw/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLAx07ZiaziGxL0FXEErXWdSqXWCNg\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"2 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":2106419,\"darkTheme\":2106419}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":3159884,\"darkTheme\":3159884}}},\"stackColor\":{\"lightTheme\":7042713,\"darkTheme\":7765395}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Pokemon Heart Gold Nuzlock Pov Lawess\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCwQ0sQMGAgiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCwQ0sQMGAgiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=tnCKxklSuVw&list=PLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"tnCKxklSuVw\",\"playlistId\":\"PLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzYlRKdXJyUHVOb2RPOUlFOFRJSDNS\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=b6708ac64952b95c&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCwQ0sQMGAgiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=tnCKxklSuVw&list=PLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R&pp=YAHIAQHwBAD4BACiBhUB15olE0fu1eVuYsD5AW2d1vnnOXo%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"tnCKxklSuVw\",\"playlistId\":\"PLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE0fu1eVuYsD5AW2d1vnnOXo%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzYlRKdXJyUHVOb2RPOUlFOFRJSDNS\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=b6708ac64952b95c&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCwQ0sQMGAgiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCwQ0sQMGAgiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=tnCKxklSuVw&list=PLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"tnCKxklSuVw\",\"playlistId\":\"PLcsWMMB25JT3bTJurrPuNodO9IE8TIH3R\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQzYlRKdXJyUHVOb2RPOUlFOFRJSDNS\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=b6708ac64952b95c&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/foy3PHSIV_c/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLDn3XsZl7UD4MQYf-wacHxjPt5X1Q\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"2 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3352876,\"darkTheme\":3352876}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":4141879,\"darkTheme\":4141879}}},\"stackColor\":{\"lightTheme\":10058886,\"darkTheme\":9664641}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Pokemon Heart Gold Nuzlocke Pov Capi\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCsQ0sQMGAkiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCsQ0sQMGAkiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=foy3PHSIV_c&list=PLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"foy3PHSIV_c\",\"playlistId\":\"PLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyZzNSSVpYQmhhUUJyTklnU2hXdlIz\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=7e8cb73c748857f7&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCsQ0sQMGAkiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=foy3PHSIV_c&list=PLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3&pp=YAHIAQHwBAD4BACiBhUB15olE9-vo4rRnwgeJFr9Sr-2a3I%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"foy3PHSIV_c\",\"playlistId\":\"PLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE9-vo4rRnwgeJFr9Sr-2a3I%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyZzNSSVpYQmhhUUJyTklnU2hXdlIz\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=7e8cb73c748857f7&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCsQ0sQMGAkiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCsQ0sQMGAkiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=foy3PHSIV_c&list=PLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"foy3PHSIV_c\",\"playlistId\":\"PLcsWMMB25JT2g3RIZXBhaQBrNIgShWvR3\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyZzNSSVpYQmhhUUJyTklnU2hXdlIz\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr2---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=7e8cb73c748857f7&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/5Vteqy_-ib4/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLBoV_UbMiEBHhFI7vDMiMYFKD8kXQ\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"10 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3355443,\"darkTheme\":3355443}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":4144959,\"darkTheme\":4144959}}},\"stackColor\":{\"lightTheme\":10066329,\"darkTheme\":9211020}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Edits\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCoQ0sQMGAoiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCoQ0sQMGAoiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=5Vteqy_-ib4&list=PLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"5Vteqy_-ib4\",\"playlistId\":\"PLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwVDFraVppaFd3ck1hYUU3TVFEOWQ3\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=e55b5eab2ffe89be&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCoQ0sQMGAoiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=5Vteqy_-ib4&list=PLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7&pp=YAHIAQHwBAD4BACiBhUB15olE718gm_XHr4dGfPjuIkBeA0%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"5Vteqy_-ib4\",\"playlistId\":\"PLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE718gm_XHr4dGfPjuIkBeA0%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwVDFraVppaFd3ck1hYUU3TVFEOWQ3\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=e55b5eab2ffe89be&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCoQ0sQMGAoiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCoQ0sQMGAoiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=5Vteqy_-ib4&list=PLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"5Vteqy_-ib4\",\"playlistId\":\"PLcsWMMB25JT0T1kiZihWwrMaaE7MQD9d7\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwVDFraVppaFd3ck1hYUU3TVFEOWQ3\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=e55b5eab2ffe89be&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/GBuHgIMvkYs/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLDxd-saT0D2h28-uImy_ijmRunN0w\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"5 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":2628880,\"darkTheme\":2628880}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":4994846,\"darkTheme\":4994846}}},\"stackColor\":{\"lightTheme\":12559749,\"darkTheme\":9668214}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"BrokenScript\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCkQ0sQMGAsiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCkQ0sQMGAsiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=GBuHgIMvkYs&list=PLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"GBuHgIMvkYs\",\"playlistId\":\"PLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxbkZNUHJRamxkUE4tRi01bjR1MDhW\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=181b8780832f918b&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCkQ0sQMGAsiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=GBuHgIMvkYs&list=PLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V&pp=YAHIAQHwBAD4BACiBhUB15olExw73-RxkIPe4vtozPh6WOc%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"GBuHgIMvkYs\",\"playlistId\":\"PLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olExw73-RxkIPe4vtozPh6WOc%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxbkZNUHJRamxkUE4tRi01bjR1MDhW\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=181b8780832f918b&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCkQ0sQMGAsiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCkQ0sQMGAsiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=GBuHgIMvkYs&list=PLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V&pp=0gcJCcwEOCosWNin\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"GBuHgIMvkYs\",\"playlistId\":\"PLcsWMMB25JT1nFMPrQjldPN-F-5n4u08V\",\"params\":\"OAI%3D\",\"playerParams\":\"0gcJCcwEOCosWNin\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxbkZNUHJRamxkUE4tRi01bjR1MDhW\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=181b8780832f918b&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/b1N4urYu9qw/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLBzo0l-cSZoSUy76SnC1bQqXVpCkw\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"3 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3355443,\"darkTheme\":3355443}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":3684408,\"darkTheme\":3684408}}},\"stackColor\":{\"lightTheme\":10066329,\"darkTheme\":9211020}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"R.E.P.O\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCgQ0sQMGAwiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCgQ0sQMGAwiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=b1N4urYu9qw&list=PLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"b1N4urYu9qw\",\"playlistId\":\"PLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyRldodFlDcXdJZWl1Q3E0b2o5Tk5H\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=6f5378bab62ef6ac&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCgQ0sQMGAwiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=b1N4urYu9qw&list=PLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG&pp=YAHIAQHwBAD4BACiBhUB15olE1g7njy9BXwyINQkormGJFs%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"b1N4urYu9qw\",\"playlistId\":\"PLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE1g7njy9BXwyINQkormGJFs%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyRldodFlDcXdJZWl1Q3E0b2o5Tk5H\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=6f5378bab62ef6ac&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCgQ0sQMGAwiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCgQ0sQMGAwiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=b1N4urYu9qw&list=PLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"b1N4urYu9qw\",\"playlistId\":\"PLcsWMMB25JT2FWhtYCqwIeiuCq4oj9NNG\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyRldodFlDcXdJZWl1Q3E0b2o5Tk5H\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=6f5378bab62ef6ac&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/PjUapNzWh5w/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLC7u5JpTW3X4DsxC82u22QdC4At7g\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"8 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3355443,\"darkTheme\":3355443}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":4144959,\"darkTheme\":4144959}}},\"stackColor\":{\"lightTheme\":10066329,\"darkTheme\":9211020}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"DND\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCcQ0sQMGA0iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT0wU5NSQmYvOlSmgrY65345\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT0wU5NSQmYvOlSmgrY65345\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT0wU5NSQmYvOlSmgrY65345\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCcQ0sQMGA0iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=PjUapNzWh5w&list=PLcsWMMB25JT0wU5NSQmYvOlSmgrY65345&pp=0gcJCcwEOCosWNin\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"PjUapNzWh5w\",\"playlistId\":\"PLcsWMMB25JT0wU5NSQmYvOlSmgrY65345\",\"params\":\"OAI%3D\",\"playerParams\":\"0gcJCcwEOCosWNin\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwd1U1TlNRbVl2T2xTbWdyWTY1MzQ1\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=3e351aa4dcd6879c&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCcQ0sQMGA0iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=PjUapNzWh5w&list=PLcsWMMB25JT0wU5NSQmYvOlSmgrY65345&pp=YAHIAQHwBAD4BACiBhUB15olE0ESNeuAWjMfzlAIlubYTOI%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"PjUapNzWh5w\",\"playlistId\":\"PLcsWMMB25JT0wU5NSQmYvOlSmgrY65345\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE0ESNeuAWjMfzlAIlubYTOI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwd1U1TlNRbVl2T2xTbWdyWTY1MzQ1\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=3e351aa4dcd6879c&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCcQ0sQMGA0iEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCcQ0sQMGA0iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=PjUapNzWh5w&list=PLcsWMMB25JT0wU5NSQmYvOlSmgrY65345\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"PjUapNzWh5w\",\"playlistId\":\"PLcsWMMB25JT0wU5NSQmYvOlSmgrY65345\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwd1U1TlNRbVl2T2xTbWdyWTY1MzQ1\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=3e351aa4dcd6879c&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/FMJ6aUs-pMk/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLAu6OPgdqtwLgIPP903k4brMGMikA\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"1 video\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":2172453,\"darkTheme\":2172453}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":3620669,\"darkTheme\":3620669}}},\"stackColor\":{\"lightTheme\":8821140,\"darkTheme\":8031368}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Lethal Company\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCYQ0sQMGA4iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCYQ0sQMGA4iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=FMJ6aUs-pMk&list=PLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"FMJ6aUs-pMk\",\"playlistId\":\"PLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxa1NsdDRsN3pBSzZhZ25WS1NrY25l\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=14c27a694b3ea4c9&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCYQ0sQMGA4iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=FMJ6aUs-pMk&list=PLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne&pp=YAHIAQHwBAD4BACiBhUB15olE7_ixiHIdPFvMVsTcobJGJM%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"FMJ6aUs-pMk\",\"playlistId\":\"PLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE7_ixiHIdPFvMVsTcobJGJM%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxa1NsdDRsN3pBSzZhZ25WS1NrY25l\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=14c27a694b3ea4c9&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCYQ0sQMGA4iEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCYQ0sQMGA4iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=FMJ6aUs-pMk&list=PLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"FMJ6aUs-pMk\",\"playlistId\":\"PLcsWMMB25JT1kSlt4l7zAK6agnVKSkcne\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxa1NsdDRsN3pBSzZhZ25WS1NrY25l\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=14c27a694b3ea4c9&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/vtf46islWE8/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLCHuYbdmn6X66Q1lipsofVOWC-wfA\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"2 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":1451571,\"darkTheme\":1451571}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":2177612,\"darkTheme\":2177612}}},\"stackColor\":{\"lightTheme\":7046553,\"darkTheme\":7767955}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Roblox\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCUQ0sQMGA8iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT0lM9SrzfYca3B4EOnt658D\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT0lM9SrzfYca3B4EOnt658D\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT0lM9SrzfYca3B4EOnt658D\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCUQ0sQMGA8iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=vtf46islWE8&list=PLcsWMMB25JT0lM9SrzfYca3B4EOnt658D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"vtf46islWE8\",\"playlistId\":\"PLcsWMMB25JT0lM9SrzfYca3B4EOnt658D\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwbE05U3J6ZlljYTNCNEVPbnQ2NThE\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=bed7f8ea2b25584f&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCUQ0sQMGA8iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=vtf46islWE8&list=PLcsWMMB25JT0lM9SrzfYca3B4EOnt658D&pp=YAHIAQHwBAD4BACiBhUB15olE7LVoRC28GlqEsvVxAHTRow%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"vtf46islWE8\",\"playlistId\":\"PLcsWMMB25JT0lM9SrzfYca3B4EOnt658D\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE7LVoRC28GlqEsvVxAHTRow%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwbE05U3J6ZlljYTNCNEVPbnQ2NThE\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=bed7f8ea2b25584f&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCUQ0sQMGA8iEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCUQ0sQMGA8iEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=vtf46islWE8&list=PLcsWMMB25JT0lM9SrzfYca3B4EOnt658D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"vtf46islWE8\",\"playlistId\":\"PLcsWMMB25JT0lM9SrzfYca3B4EOnt658D\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwbE05U3J6ZlljYTNCNEVPbnQ2NThE\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr1---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=bed7f8ea2b25584f&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/IoG5hIz-SFU/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLBGk4WmVfn76IM-CWhJFMWZEMfzgw\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"9 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3350813,\"darkTheme\":3350813}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":4993579,\"darkTheme\":4993579}}},\"stackColor\":{\"lightTheme\":10057067,\"darkTheme\":9665654}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Jackbox\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCQQ0sQMGBAiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCQQ0sQMGBAiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=IoG5hIz-SFU&list=PLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"IoG5hIz-SFU\",\"playlistId\":\"PLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwVVlxY3hWcHhRUzRQMDhxaFhHMmN0\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2281b9848cfe4855&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCQQ0sQMGBAiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=IoG5hIz-SFU&list=PLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct&pp=YAHIAQHwBAD4BACiBhUB15olE3IWdn04aGaqnX66d8I5Rek%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"IoG5hIz-SFU\",\"playlistId\":\"PLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE3IWdn04aGaqnX66d8I5Rek%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwVVlxY3hWcHhRUzRQMDhxaFhHMmN0\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2281b9848cfe4855&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCQQ0sQMGBAiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCQQ0sQMGBAiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=IoG5hIz-SFU&list=PLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"IoG5hIz-SFU\",\"playlistId\":\"PLcsWMMB25JT0UYqcxVpxQS4P08qhXG2ct\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQwVVlxY3hWcHhRUzRQMDhxaFhHMmN0\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8aje.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=2281b9848cfe4855&ip=79.116.226.56&initcwndbps=2475000&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/fYJgtnmYfLE/hqdefault.jpg?sqp=-oaymwExCOADEI4CSFryq4qpAyMIARUAAIhCGAHwAQH4Af4DgAKAA4oCDAgAEAEYfyBcKBUwDw==&rs=AOn4CLA-2qQnHgsEqARRjxP_exAu2WhNiw\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"4 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":2628870,\"darkTheme\":2628870}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":5849102,\"darkTheme\":5849102}}},\"stackColor\":{\"lightTheme\":12561541,\"darkTheme\":9208432}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Ace Attorney\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCMQ0sQMGBEiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCMQ0sQMGBEiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=fYJgtnmYfLE&list=PLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"fYJgtnmYfLE\",\"playlistId\":\"PLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQydVhtSnhxUEF6SzFad3habUR2OFhF\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=7d8260b679987cb1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCMQ0sQMGBEiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=fYJgtnmYfLE&list=PLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE&pp=YAHIAQHwBAD4BACiBhUB15olEwhStC-gTCHtriUPilwhlBE%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"fYJgtnmYfLE\",\"playlistId\":\"PLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olEwhStC-gTCHtriUPilwhlBE%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQydVhtSnhxUEF6SzFad3habUR2OFhF\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=7d8260b679987cb1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCMQ0sQMGBEiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCMQ0sQMGBEiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=fYJgtnmYfLE&list=PLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"fYJgtnmYfLE\",\"playlistId\":\"PLcsWMMB25JT2uXmJxqPAzK1ZwxZmDv8XE\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQydVhtSnhxUEF6SzFad3habUR2OFhF\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr4---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=7d8260b679987cb1&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/1sSPfMU2cGQ/hqdefault.jpg?sqp=-oaymwExCOADEI4CSFryq4qpAyMIARUAAIhCGAHwAQH4Af4JgALQBYoCDAgAEAEYfyATKCYwDw==&rs=AOn4CLB7ejZo7zm26VVBN2n1BxS07Q0PZg\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"4 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":3344143,\"darkTheme\":3344143}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":5836059,\"darkTheme\":5836059}}},\"stackColor\":{\"lightTheme\":10054515,\"darkTheme\":9664123}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Slanders\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCIQ0sQMGBIiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCIQ0sQMGBIiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=1sSPfMU2cGQ&list=PLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"1sSPfMU2cGQ\",\"playlistId\":\"PLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxR0NuWjRQNzIyeEsyTmd4NEJCWTgz\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=d6c48f7cc5367064&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCIQ0sQMGBIiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=1sSPfMU2cGQ&list=PLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83&pp=YAHIAQHwBAD4BACiBhUB15olE5CjjgWVNzjGYY9pWgpzm0I%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"1sSPfMU2cGQ\",\"playlistId\":\"PLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE5CjjgWVNzjGYY9pWgpzm0I%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxR0NuWjRQNzIyeEsyTmd4NEJCWTgz\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=d6c48f7cc5367064&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCIQ0sQMGBIiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCIQ0sQMGBIiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=1sSPfMU2cGQ&list=PLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"1sSPfMU2cGQ\",\"playlistId\":\"PLcsWMMB25JT1GCnZ4P722xK2Ngx4BBY83\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQxR0NuWjRQNzIyeEsyTmd4NEJCWTgz\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr3---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=d6c48f7cc5367064&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/6JK_3bUlIdM/hqdefault.jpg?sqp=-oaymwExCOADEI4CSFryq4qpAyMIARUAAIhCGAHwAQH4Af4JgALQBYoCDAgAEAEYLyBLKH8wDw==&rs=AOn4CLBIr5IY-ofRafcOLHxFQ4hf0aAvlQ\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"2 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":1187379,\"darkTheme\":1187379}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":2176089,\"darkTheme\":2176089}}},\"stackColor\":{\"lightTheme\":7043993,\"darkTheme\":7766163}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Escalas de poder\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCEQ0sQMGBMiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCEQ0sQMGBMiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=6JK_3bUlIdM&list=PLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"6JK_3bUlIdM\",\"playlistId\":\"PLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyV2pmSDhuTG52Y0tSQmQzbGVxV3N1\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=e892bfddb52521d3&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCEQ0sQMGBMiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=6JK_3bUlIdM&list=PLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu&pp=YAHIAQHwBAD4BACiBhUB15olE9P_h_PC_ibcx7Ke5a-EWEk%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"6JK_3bUlIdM\",\"playlistId\":\"PLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE9P_h_PC_ibcx7Ke5a-EWEk%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyV2pmSDhuTG52Y0tSQmQzbGVxV3N1\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=e892bfddb52521d3&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCEQ0sQMGBMiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCEQ0sQMGBMiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=6JK_3bUlIdM&list=PLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"6JK_3bUlIdM\",\"playlistId\":\"PLcsWMMB25JT2WjfH8nLnvcKRBd3leqWsu\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyV2pmSDhuTG52Y0tSQmQzbGVxV3N1\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=e892bfddb52521d3&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}},{\"lockupViewModel\":{\"contentImage\":{\"collectionThumbnailViewModel\":{\"primaryThumbnail\":{\"thumbnailViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://i.ytimg.com/vi/3ms_c_j5HwU/hqdefault.jpg?sqp=-oaymwEXCOADEI4CSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLCMByufHK2c5eeAONGut90XtLl-LQ\",\"width\":480,\"height\":270}]},\"overlays\":[{\"thumbnailOverlayBadgeViewModel\":{\"thumbnailBadges\":[{\"thumbnailBadgeViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAYLISTS\"}}]},\"text\":\"3 videos\",\"badgeStyle\":\"THUMBNAIL_OVERLAY_BADGE_STYLE_DEFAULT\",\"backgroundColor\":{\"lightTheme\":2369548,\"darkTheme\":2369548}}}],\"position\":\"THUMBNAIL_OVERLAY_BADGE_POSITION_BOTTOM_END\"}},{\"thumbnailHoverOverlayViewModel\":{\"icon\":{\"sources\":[{\"clientResource\":{\"imageName\":\"PLAY_ALL\"}}]},\"text\":{\"content\":\"Play all\",\"styleRuns\":[{\"startIndex\":0,\"length\":8}]},\"style\":\"THUMBNAIL_HOVER_OVERLAY_STYLE_COVER\"}}],\"backgroundColor\":{\"lightTheme\":3686163,\"darkTheme\":3686163}}},\"stackColor\":{\"lightTheme\":11911045,\"darkTheme\":8883312}}},\"metadata\":{\"lockupMetadataViewModel\":{\"title\":{\"content\":\"Destiny 2\"},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"View full playlist\",\"commandRuns\":[{\"startIndex\":0,\"length\":18,\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCAQ0sQMGBQiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaMoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/playlist?list=PLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK\",\"webPageType\":\"WEB_PAGE_TYPE_PLAYLIST\",\"rootVe\":5754,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"VLPLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK\"}}}}],\"styleRuns\":[{\"startIndex\":0,\"length\":18,\"weightLabel\":\"FONT_WEIGHT_MEDIUM\"}]}}]}],\"delimiter\":\" • \"}}}},\"contentId\":\"PLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK\",\"contentType\":\"LOCKUP_CONTENT_TYPE_PLAYLIST\",\"itemPlayback\":{\"inlinePlayerData\":{\"onSelect\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCAQ0sQMGBQiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=3ms_c_j5HwU&list=PLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"3ms_c_j5HwU\",\"playlistId\":\"PLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyaTRKR0tYVEVyVFZlY1FjcUdNaGRL\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=de6b3f73f8f91f05&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}},\"onVisible\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCAQ0sQMGBQiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=3ms_c_j5HwU&list=PLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK&pp=YAHIAQHwBAD4BACiBhUB15olE7KxmbaS7rYQHzQBKg-HJs4%3D\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"3ms_c_j5HwU\",\"playlistId\":\"PLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK\",\"playerParams\":\"YAHIAQHwBAD4BACiBhUB15olE7KxmbaS7rYQHzQBKg-HJs4%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyaTRKR0tYVEVyVFZlY1FjcUdNaGRL\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=de6b3f73f8f91f05&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CCAQ0sQMGBQiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"visibility\":{\"types\":\"12\"}}},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CCAQ0sQMGBQiEwir8_i03d-UAxVj40kHHe6WIcYyBmctaGlnaFoYVUNDV2ZVWGZQZ3AzREVCcFBlbV9MUU93mgEFEPI4GGjKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/watch?v=3ms_c_j5HwU&list=PLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK\",\"webPageType\":\"WEB_PAGE_TYPE_WATCH\",\"rootVe\":3832}},\"watchEndpoint\":{\"videoId\":\"3ms_c_j5HwU\",\"playlistId\":\"PLcsWMMB25JT2i4JGKXTErTVecQcqGMhdK\",\"params\":\"OAI%3D\",\"loggingContext\":{\"vssLoggingContext\":{\"serializedContextData\":\"GiJQTGNzV01NQjI1SlQyaTRKR0tYVEVyVFZlY1FjcUdNaGRL\"}},\"watchEndpointSupportedOnesieConfig\":{\"html5PlaybackOnesieConfig\":{\"commonConfig\":{\"url\":\"https://rr5---sn-gqn-8ajl.googlevideo.com/initplayback?source=youtube&oeis=1&c=WEB&oad=3200&ovd=3200&oaad=11000&oavd=11000&ocs=700&oewis=1&oputc=1&ofpcc=1&msp=1&odepv=1&id=de6b3f73f8f91f05&ip=79.116.226.56&initcwndbps=2377500&mt=1780099509&oweuc=\"}}}}}}}}}}],\"trackingParams\":\"CB8Q6IsCGAAiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"targetId\":\"browse-feedUCCWfUXfPgp3DEBpPem_LQOwplaylists104\"}}],\"trackingParams\":\"CB4Quy8YACITCKvz-LTd35QDFWPjSQcd7pYhxg==\"}}],\"trackingParams\":\"CBoQui8iEwir8_i03d-UAxVj40kHHe6WIcY=\",\"subMenu\":{\"channelSubMenuRenderer\":{\"sortSetting\":{\"sortFilterSubMenuRenderer\":{\"subMenuItems\":[{\"title\":\"Date added (newest)\",\"selected\":true,\"navigationEndpoint\":{\"clickTrackingParams\":\"CB0Q25cSGAAiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/playlists?view=1&sort=dd&flow=grid\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EglwbGF5bGlzdHMYAyABMAHyBgQKAkIA\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"trackingParams\":\"CB0Q25cSGAAiEwir8_i03d-UAxVj40kHHe6WIcY=\"},{\"title\":\"Last video added\",\"selected\":false,\"navigationEndpoint\":{\"clickTrackingParams\":\"CBwQ3JcSGAEiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/playlists?view=1&sort=lad&flow=grid\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EglwbGF5bGlzdHMYBCABMAHyBgQKAkIA\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"trackingParams\":\"CBwQ3JcSGAEiEwir8_i03d-UAxVj40kHHe6WIcY=\"}],\"title\":\"Sort by\",\"icon\":{\"iconType\":\"SORT\"},\"accessibility\":{\"accessibilityData\":{\"label\":\"Sort by\"}},\"trackingParams\":\"CBsQgdoEIhMIq_P4tN3flAMVY-NJBx3uliHG\"}}}},\"targetId\":\"browse-feedUCCWfUXfPgp3DEBpPem_LQOwplaylists\",\"disablePullToRefresh\":true}},\"trackingParams\":\"CBkQ8JMBGAYiEwir8_i03d-UAxVj40kHHe6WIcY=\"}},{\"expandableTabRenderer\":{\"endpoint\":{\"clickTrackingParams\":\"CAAQhGciEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/search\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EgZzZWFyY2jyBgQKAloA\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"title\":\"Search\",\"selected\":false}}]}},\"header\":{\"pageHeaderRenderer\":{\"pageTitle\":\":melondeagua: archive\",\"content\":{\"pageHeaderViewModel\":{\"title\":{\"dynamicTextViewModel\":{\"text\":{\"content\":\":melondeagua: archive\"},\"maxLines\":2,\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CBgQj-QKIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"visibility\":{\"types\":\"12\"}}}}}},\"image\":{\"decoratedAvatarViewModel\":{\"avatar\":{\"avatarViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s72-c-k-c0x00ffffff-no-rj\",\"width\":72,\"height\":72},{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s120-c-k-c0x00ffffff-no-rj\",\"width\":120,\"height\":120},{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s160-c-k-c0x00ffffff-no-rj\",\"width\":160,\"height\":160}],\"processor\":{\"borderImageProcessor\":{\"circular\":true}}},\"avatarImageSize\":\"AVATAR_SIZE_XL\",\"loggingDirectives\":{\"trackingParams\":\"CBcQ6OENIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"visibility\":{\"types\":\"12\"}}}}}},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"@melondeaguaarchive\",\"styleRuns\":[{\"weightLabel\":\"FONT_WEIGHT_MEDIUM\",\"styleRunExtensions\":{\"styleRunColorMapExtension\":{\"colorMap\":[{\"key\":\"USER_INTERFACE_THEME_DARK\",\"value\":4294967295},{\"key\":\"USER_INTERFACE_THEME_LIGHT\",\"value\":4279440147}]}}}]},\"enableTruncation\":true}]},{\"metadataParts\":[{\"text\":{\"content\":\"16 subscribers\"},\"accessibilityLabel\":\"16 subscribers\"}]}],\"delimiter\":\"•\",\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CBYQ9eQKIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"visibility\":{\"types\":\"12\"}}}}}},\"actions\":{\"flexibleActionsViewModel\":{\"actionsRows\":[{\"actions\":[{\"buttonViewModel\":{\"title\":\"Subscribe\",\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CA8Qot8KIhMIq_P4tN3flAMVY-NJBx3uliHGygEEdk4Q3A==\",\"commandMetadata\":{\"webCommandMetadata\":{\"ignoreNavigation\":true}},\"modalEndpoint\":{\"modal\":{\"modalWithTitleAndButtonRenderer\":{\"title\":{\"simpleText\":\"Want to subscribe to this channel?\"},\"content\":{\"simpleText\":\"Sign in to subscribe to this channel.\"},\"button\":{\"buttonRenderer\":{\"style\":\"STYLE_MONO_FILLED\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"text\":{\"simpleText\":\"Sign in\"},\"navigationEndpoint\":{\"clickTrackingParams\":\"CBUQ_YYEIhMIq_P4tN3flAMVY-NJBx3uliHGMglzdWJzY3JpYmXKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"https://accounts.google.com/ServiceLogin?service=youtube&uilel=3&passive=true&continue=https%3A%2F%2Fwww.youtube.com%2Fsignin%3Faction_handle_signin%3Dtrue%26app%3Ddesktop%26hl%3Den%26next%3D%252F%2540melondeaguaarchive%252Fplaylists%26continue_action%3DQUFFLUhqa2dJR2dlTnFjLXNZT0IzYnhPSnN6ODdwRk00Z3xBQ3Jtc0tuTWZ2NC1kdXd6YUpFUnk0TEZlbS1UenFTMmEwbE1ZRmhUUmJreGxRNWlDQVJJa0RXS0VaTkpHNE5LWURqYUIwRGNxWGN5blJFelFRelRGYno4Sm4xQmpwZ0hQMDYwXzhTYzl1cHBZaDN2b3Bnb3c2bjRCM3FWbm90eU1YdUlRMG9oeHZFeFhaeHR1d0l2Z0h2ZEljOEoyYVYwNFBUemZDeFNHeWpsRDN0UE5xQnFhdERhMnlMVldUOFVDTksxejA0MEw2VlY&hl=en&ec=66429\",\"webPageType\":\"WEB_PAGE_TYPE_UNKNOWN\",\"rootVe\":83769}},\"signInEndpoint\":{\"nextEndpoint\":{\"clickTrackingParams\":\"CBUQ_YYEIhMIq_P4tN3flAMVY-NJBx3uliHGygEEdk4Q3A==\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/playlists\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EglwbGF5bGlzdHM%3D\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"continueAction\":\"QUFFLUhqa2dJR2dlTnFjLXNZT0IzYnhPSnN6ODdwRk00Z3xBQ3Jtc0tuTWZ2NC1kdXd6YUpFUnk0TEZlbS1UenFTMmEwbE1ZRmhUUmJreGxRNWlDQVJJa0RXS0VaTkpHNE5LWURqYUIwRGNxWGN5blJFelFRelRGYno4Sm4xQmpwZ0hQMDYwXzhTYzl1cHBZaDN2b3Bnb3c2bjRCM3FWbm90eU1YdUlRMG9oeHZFeFhaeHR1d0l2Z0h2ZEljOEoyYVYwNFBUemZDeFNHeWpsRDN0UE5xQnFhdERhMnlMVldUOFVDTksxejA0MEw2VlY\",\"idamTag\":\"66429\"}},\"trackingParams\":\"CBUQ_YYEIhMIq_P4tN3flAMVY-NJBx3uliHG\"}}}}}}},\"accessibilityText\":\"Subscribe\",\"style\":\"BUTTON_VIEW_MODEL_STYLE_MONO\",\"trackingParams\":\"CA8Qot8KIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"isFullWidth\":false,\"type\":\"BUTTON_VIEW_MODEL_TYPE_FILLED\",\"buttonSize\":\"BUTTON_VIEW_MODEL_SIZE_DEFAULT\",\"state\":\"BUTTON_VIEW_MODEL_STATE_ACTIVE\"}}]}],\"justifyContent\":\"FLEXIBLE_ACTIONS_JUSTIFY_CONTENT_START\",\"minimumRowHeight\":44,\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CA8Qot8KIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"visibility\":{\"types\":\"12\"},\"clientVeSpec\":{\"uiType\":184974,\"veCounter\":364958616}}}}}},\"description\":{\"descriptionPreviewViewModel\":{\"description\":{\"content\":\"yo, soy, el melon de agua \"},\"maxLines\":2,\"truncationText\":{\"content\":\"...more\",\"styleRuns\":[{\"startIndex\":0,\"length\":7,\"weight\":500}]},\"alwaysShowTruncationText\":true,\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CBAQr_4KIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"visibility\":{\"types\":\"12\"}}},\"accessibilityContext\":{\"label\":\"Description. yo, soy, el melon de agua...tap for more.\"},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CBAQr_4KIhMIq_P4tN3flAMVY-NJBx3uliHGygEEdk4Q3A==\",\"showEngagementPanelEndpoint\":{\"engagementPanel\":{\"engagementPanelSectionListRenderer\":{\"header\":{\"engagementPanelTitleHeaderRenderer\":{\"title\":{\"simpleText\":\":melondeagua: archive\"},\"visibilityButton\":{\"buttonRenderer\":{\"style\":\"STYLE_DEFAULT\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"icon\":{\"iconType\":\"CLOSE\"},\"accessibility\":{\"label\":\"Close\"},\"trackingParams\":\"CBQQ8FsiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"accessibilityData\":{\"accessibilityData\":{\"label\":\"Close\"}},\"command\":{\"clickTrackingParams\":\"CBQQ8FsiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"changeEngagementPanelVisibilityAction\":{\"targetId\":\"6b52460b-0000-26ee-a5a8-14c14ee6a91c\",\"visibility\":\"ENGAGEMENT_PANEL_VISIBILITY_HIDDEN\"}}}},\"trackingParams\":\"CBEQ040EIhMIq_P4tN3flAMVY-NJBx3uliHG\"}},\"content\":{\"sectionListRenderer\":{\"contents\":[{\"itemSectionRenderer\":{\"contents\":[{\"continuationItemRenderer\":{\"trigger\":\"CONTINUATION_TRIGGER_ON_ITEM_SHOWN\",\"continuationEndpoint\":{\"clickTrackingParams\":\"CBMQuy8YACITCKvz-LTd35QDFWPjSQcd7pYhxsoBBHZOENw=\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true,\"apiUrl\":\"/youtubei/v1/browse\"}},\"continuationCommand\":{\"token\":\"4qmFsgJgEhhVQ0NXZlVYZlBncDNERUJwUGVtX0xRT3caRDhnWXJHaW1hQVNZS0pEWmlOVEkwTmpCakxUQXdNREF0TWpabFpTMWhOV0U0TFRFMFl6RTBaV1UyWVRreFl3JTNEJTNE\",\"request\":\"CONTINUATION_REQUEST_TYPE_BROWSE\"}}}}],\"trackingParams\":\"CBMQuy8YACITCKvz-LTd35QDFWPjSQcd7pYhxg==\",\"sectionIdentifier\":\"6b52460c-0000-26ee-a5a8-14c14ee6a91c\",\"targetId\":\"6b52460c-0000-26ee-a5a8-14c14ee6a91c\"}}],\"trackingParams\":\"CBIQui8iEwir8_i03d-UAxVj40kHHe6WIcY=\",\"scrollPaneStyle\":{\"scrollable\":true}}},\"targetId\":\"6b52460b-0000-26ee-a5a8-14c14ee6a91c\",\"identifier\":{\"surface\":\"ENGAGEMENT_PANEL_SURFACE_BROWSE\",\"tag\":\"6b52460b-0000-26ee-a5a8-14c14ee6a91c\"}}},\"identifier\":{\"surface\":\"ENGAGEMENT_PANEL_SURFACE_BROWSE\",\"tag\":\"6b52460b-0000-26ee-a5a8-14c14ee6a91c\"},\"engagementPanelPresentationConfigs\":{\"engagementPanelPopupPresentationConfig\":{\"popupType\":\"PANEL_POPUP_TYPE_DIALOG\"}}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CA8Qot8KIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"visibility\":{\"types\":\"12\"}}}}}}}},\"metadata\":{\"channelMetadataRenderer\":{\"title\":\":melondeagua: archive\",\"description\":\"yo, soy, el melon de agua\",\"rssUrl\":\"https://www.youtube.com/feeds/videos.xml?channel_id=UCCWfUXfPgp3DEBpPem_LQOw\",\"externalId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"keywords\":\"\",\"ownerUrls\":[\"http://www.youtube.com/@melondeaguaarchive\"],\"avatar\":{\"thumbnails\":[{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s900-c-k-c0x00ffffff-no-rj\",\"width\":900,\"height\":900}]},\"channelUrl\":\"https://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"isFamilySafe\":true,\"availableCountryCodes\":[\"MY\",\"DM\",\"US\",\"GB\",\"GR\",\"NU\",\"MV\",\"GI\",\"SK\",\"ZW\",\"FM\",\"UM\",\"MZ\",\"ES\",\"IL\",\"CA\",\"HR\",\"DK\",\"MH\",\"PR\",\"JO\",\"YT\",\"GW\",\"CG\",\"GT\",\"BM\",\"IM\",\"ST\",\"WF\",\"AD\",\"KM\",\"DZ\",\"BD\",\"GU\",\"JM\",\"BG\",\"DO\",\"SY\",\"AX\",\"PL\",\"PS\",\"OM\",\"SH\",\"ET\",\"IT\",\"CW\",\"KW\",\"AO\",\"BV\",\"LR\",\"LS\",\"BW\",\"TO\",\"FK\",\"CL\",\"SZ\",\"CR\",\"LA\",\"SE\",\"GH\",\"BO\",\"GN\",\"SD\",\"HM\",\"GD\",\"RO\",\"TW\",\"SM\",\"CN\",\"AS\",\"VN\",\"CI\",\"NI\",\"DE\",\"CV\",\"VC\",\"CC\",\"BQ\",\"MP\",\"AG\",\"KR\",\"BH\",\"VI\",\"LT\",\"BN\",\"VU\",\"CX\",\"IS\",\"BA\",\"AT\",\"GS\",\"KP\",\"FO\",\"SO\",\"GF\",\"SX\",\"NG\",\"TD\",\"LK\",\"MS\",\"AZ\",\"YE\",\"IR\",\"MK\",\"MN\",\"RE\",\"SG\",\"KE\",\"KI\",\"CD\",\"CY\",\"AW\",\"KN\",\"NC\",\"PG\",\"AM\",\"KH\",\"PE\",\"PT\",\"TF\",\"EH\",\"EC\",\"GL\",\"UA\",\"MT\",\"ME\",\"TL\",\"BY\",\"BE\",\"HT\",\"LY\",\"MW\",\"SI\",\"AQ\",\"MF\",\"TN\",\"TK\",\"MA\",\"MD\",\"LI\",\"HN\",\"AR\",\"PN\",\"SL\",\"NO\",\"GM\",\"PA\",\"SN\",\"IN\",\"PH\",\"SR\",\"IO\",\"HK\",\"AF\",\"TV\",\"SA\",\"MC\",\"FI\",\"EE\",\"BL\",\"LV\",\"VG\",\"MQ\",\"NR\",\"QA\",\"AL\",\"NZ\",\"BR\",\"CK\",\"TJ\",\"VA\",\"IQ\",\"UZ\",\"GQ\",\"AI\",\"MG\",\"ML\",\"GY\",\"RW\",\"KY\",\"MO\",\"TZ\",\"LU\",\"HU\",\"ID\",\"MX\",\"PW\",\"SV\",\"GG\",\"NL\",\"CH\",\"TC\",\"PF\",\"BI\",\"ZA\",\"MR\",\"JP\",\"BB\",\"ER\",\"LC\",\"BF\",\"TH\",\"TM\",\"JE\",\"MU\",\"RU\",\"SJ\",\"BT\",\"EG\",\"NA\",\"NF\",\"PM\",\"SS\",\"FR\",\"NE\",\"DJ\",\"BZ\",\"UG\",\"NP\",\"PK\",\"CM\",\"TR\",\"KZ\",\"IE\",\"MM\",\"AU\",\"PY\",\"TT\",\"KG\",\"VE\",\"SC\",\"GE\",\"SB\",\"BJ\",\"UY\",\"RS\",\"TG\",\"CO\",\"BS\",\"AE\",\"CF\",\"CU\",\"GP\",\"GA\",\"CZ\",\"WS\",\"FJ\",\"LB\",\"ZM\"],\"androidDeepLink\":\"android-app://com.google.android.youtube/http/www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"androidAppindexingLink\":\"android-app://com.google.android.youtube/http/www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"iosAppindexingLink\":\"ios-app://544007664/vnd.youtube/www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"vanityChannelUrl\":\"http://www.youtube.com/@melondeaguaarchive\"}},\"trackingParams\":\"CAAQhGciEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"topbar\":{\"desktopTopbarRenderer\":{\"logo\":{\"topbarLogoRenderer\":{\"iconImage\":{\"iconType\":\"YOUTUBE_LOGO\"},\"tooltipText\":{\"runs\":[{\"text\":\"YouTube Home\"}]},\"endpoint\":{\"clickTrackingParams\":\"CA4QsV4iEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/\",\"webPageType\":\"WEB_PAGE_TYPE_BROWSE\",\"rootVe\":3854,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"FEwhat_to_watch\"}},\"trackingParams\":\"CA4QsV4iEwir8_i03d-UAxVj40kHHe6WIcY=\",\"overrideEntityKey\":\"EgZ0b3BiYXIg9QEoAQ%3D%3D\"}},\"searchbox\":{\"fusionSearchboxRenderer\":{\"icon\":{\"iconType\":\"SEARCH\"},\"placeholderText\":{\"runs\":[{\"text\":\"Search\"}]},\"config\":{\"webSearchboxConfig\":{\"requestLanguage\":\"en\",\"requestDomain\":\"us\",\"hasOnscreenKeyboard\":false,\"focusSearchbox\":true}},\"trackingParams\":\"CAoQ7VAiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"searchEndpoint\":{\"clickTrackingParams\":\"CAoQ7VAiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/results?search_query=\",\"webPageType\":\"WEB_PAGE_TYPE_SEARCH\",\"rootVe\":4724}},\"searchEndpoint\":{\"query\":\"\"}},\"clearButton\":{\"buttonRenderer\":{\"style\":\"STYLE_DEFAULT\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"icon\":{\"iconType\":\"CLOSE\"},\"trackingParams\":\"CA0Q8FsiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"accessibilityData\":{\"accessibilityData\":{\"label\":\"Clear search query\"}}}},\"showImageSourceDialog\":{\"clickTrackingParams\":\"CAoQ7VAiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"showDialogCommand\":{\"panelLoadingStrategy\":{\"inlineContent\":{\"dialogViewModel\":{\"header\":{\"dialogHeaderViewModel\":{\"headline\":{\"content\":\"Image source\"}}},\"footer\":{\"panelFooterViewModel\":{\"primaryButton\":{\"buttonViewModel\":{\"title\":\"Visit source\",\"style\":\"BUTTON_VIEW_MODEL_STYLE_MONO\",\"trackingParams\":\"CAwQ8FsiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"isFullWidth\":true,\"type\":\"BUTTON_VIEW_MODEL_TYPE_FILLED\"}},\"secondaryButton\":{\"buttonViewModel\":{\"title\":\"Cancel\",\"style\":\"BUTTON_VIEW_MODEL_STYLE_MONO\",\"trackingParams\":\"CAsQ8FsiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"isFullWidth\":true,\"type\":\"BUTTON_VIEW_MODEL_TYPE_TONAL\"}},\"shouldHideDivider\":true}},\"content\":{\"basicContentViewModel\":{\"paragraphs\":[{\"text\":{\"content\":\"Visit image source website?\"}}]}}}}}}}}},\"trackingParams\":\"CAEQq6wBIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"topbarButtons\":[{\"topbarMenuButtonRenderer\":{\"icon\":{\"iconType\":\"MORE_VERT\"},\"menuRequest\":{\"clickTrackingParams\":\"CAgQ_qsBGAAiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true,\"apiUrl\":\"/youtubei/v1/account/account_menu\"}},\"signalServiceEndpoint\":{\"signal\":\"GET_ACCOUNT_MENU\",\"actions\":[{\"clickTrackingParams\":\"CAgQ_qsBGAAiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"openPopupAction\":{\"popup\":{\"multiPageMenuRenderer\":{\"trackingParams\":\"CAkQ_6sBIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"style\":\"MULTI_PAGE_MENU_STYLE_TYPE_SYSTEM\",\"showLoadingSpinner\":true}},\"popupType\":\"DROPDOWN\",\"beReused\":true}}]}},\"trackingParams\":\"CAgQ_qsBGAAiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"accessibility\":{\"accessibilityData\":{\"label\":\"Settings\"}},\"tooltip\":\"Settings\",\"style\":\"STYLE_DEFAULT\"}},{\"buttonRenderer\":{\"style\":\"STYLE_SUGGESTIVE\",\"size\":\"SIZE_SMALL\",\"text\":{\"runs\":[{\"text\":\"Sign in\"}]},\"icon\":{\"iconType\":\"AVATAR_LOGGED_OUT\"},\"navigationEndpoint\":{\"clickTrackingParams\":\"CAcQ1IAEGAEiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"https://accounts.google.com/ServiceLogin?service=youtube&uilel=3&passive=true&continue=https%3A%2F%2Fwww.youtube.com%2Fsignin%3Faction_handle_signin%3Dtrue%26app%3Ddesktop%26hl%3Den%26next%3Dhttps%253A%252F%252Fwww.youtube.com%252Fyoutubei%252Fv1%252Fbrowse%253Fkey%253DAIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8%2526prettyPrint%253Dfalse&hl=en&ec=65620\",\"webPageType\":\"WEB_PAGE_TYPE_UNKNOWN\",\"rootVe\":83769}},\"signInEndpoint\":{\"idamTag\":\"65620\"}},\"trackingParams\":\"CAcQ1IAEGAEiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"targetId\":\"topbar-signin\"}}],\"hotkeyDialog\":{\"hotkeyDialogRenderer\":{\"title\":{\"runs\":[{\"text\":\"Keyboard shortcuts\"}]},\"sections\":[{\"hotkeyDialogSectionRenderer\":{\"title\":{\"runs\":[{\"text\":\"Playback\"}]},\"options\":[{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle play/pause\"}]},\"hotkey\":\"k\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rewind 10 seconds\"}]},\"hotkey\":\"j\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Fast forward 10 seconds\"}]},\"hotkey\":\"l\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Previous video\"}]},\"hotkey\":\"P (SHIFT+p)\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Next video\"}]},\"hotkey\":\"N (SHIFT+n)\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Previous frame (while paused)\"}]},\"hotkey\":\",\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Comma\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Next frame (while paused)\"}]},\"hotkey\":\".\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Period\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Decrease playback rate\"}]},\"hotkey\":\"\\u003c (SHIFT+,)\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Less than or SHIFT + comma\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Increase playback rate\"}]},\"hotkey\":\"\\u003e (SHIFT+.)\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Greater than or SHIFT + period\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Seek to specific point in the video (7 advances to 70% of duration)\"}]},\"hotkey\":\"0..9\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Seek to previous chapter\"}]},\"hotkey\":\"CONTROL + ←\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Seek to next chapter\"}]},\"hotkey\":\"CONTROL + →\"}}]}},{\"hotkeyDialogSectionRenderer\":{\"title\":{\"runs\":[{\"text\":\"General\"}]},\"options\":[{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle full screen\"}]},\"hotkey\":\"f\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle theater mode\"}]},\"hotkey\":\"t\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle miniplayer\"}]},\"hotkey\":\"i\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Close miniplayer or current dialog\"}]},\"hotkey\":\"ESCAPE\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle mute\"}]},\"hotkey\":\"m\"}}]}},{\"hotkeyDialogSectionRenderer\":{\"title\":{\"runs\":[{\"text\":\"Subtitles and closed captions\"}]},\"options\":[{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"If the video supports captions, toggle captions ON/OFF\"}]},\"hotkey\":\"c\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rotate through different text opacity levels\"}]},\"hotkey\":\"o\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rotate through different window opacity levels\"}]},\"hotkey\":\"w\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rotate through font sizes (increasing)\"}]},\"hotkey\":\"+\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rotate through font sizes (decreasing)\"}]},\"hotkey\":\"-\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Minus\"}}}}]}},{\"hotkeyDialogSectionRenderer\":{\"title\":{\"runs\":[{\"text\":\"Spherical Videos\"}]},\"options\":[{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Pan up\"}]},\"hotkey\":\"w\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Pan left\"}]},\"hotkey\":\"a\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Pan down\"}]},\"hotkey\":\"s\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Pan right\"}]},\"hotkey\":\"d\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Zoom in\"}]},\"hotkey\":\"+ on numpad or ]\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Plus on number pad or right bracket\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Zoom out\"}]},\"hotkey\":\"- on numpad or [\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Minus on number pad or left bracket\"}}}}]}}],\"dismissButton\":{\"buttonRenderer\":{\"style\":\"STYLE_BLUE_TEXT\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"text\":{\"runs\":[{\"text\":\"Dismiss\"}]},\"trackingParams\":\"CAYQ8FsiEwir8_i03d-UAxVj40kHHe6WIcY=\"}},\"trackingParams\":\"CAUQteYDIhMIq_P4tN3flAMVY-NJBx3uliHG\"}},\"backButton\":{\"buttonRenderer\":{\"trackingParams\":\"CAQQvIYDIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"command\":{\"clickTrackingParams\":\"CAQQvIYDIhMIq_P4tN3flAMVY-NJBx3uliHGygEEdk4Q3A==\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true}},\"signalServiceEndpoint\":{\"signal\":\"CLIENT_SIGNAL\",\"actions\":[{\"clickTrackingParams\":\"CAQQvIYDIhMIq_P4tN3flAMVY-NJBx3uliHGygEEdk4Q3A==\",\"signalAction\":{\"signal\":\"HISTORY_BACK\"}}]}}}},\"forwardButton\":{\"buttonRenderer\":{\"trackingParams\":\"CAMQvYYDIhMIq_P4tN3flAMVY-NJBx3uliHG\",\"command\":{\"clickTrackingParams\":\"CAMQvYYDIhMIq_P4tN3flAMVY-NJBx3uliHGygEEdk4Q3A==\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true}},\"signalServiceEndpoint\":{\"signal\":\"CLIENT_SIGNAL\",\"actions\":[{\"clickTrackingParams\":\"CAMQvYYDIhMIq_P4tN3flAMVY-NJBx3uliHGygEEdk4Q3A==\",\"signalAction\":{\"signal\":\"HISTORY_FORWARD\"}}]}}}},\"a11ySkipNavigationButton\":{\"buttonRenderer\":{\"style\":\"STYLE_DEFAULT\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"text\":{\"runs\":[{\"text\":\"Skip navigation\"}]},\"trackingParams\":\"CAIQ8FsiEwir8_i03d-UAxVj40kHHe6WIcY=\",\"command\":{\"clickTrackingParams\":\"CAIQ8FsiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true}},\"signalServiceEndpoint\":{\"signal\":\"CLIENT_SIGNAL\",\"actions\":[{\"clickTrackingParams\":\"CAIQ8FsiEwir8_i03d-UAxVj40kHHe6WIcbKAQR2ThDc\",\"signalAction\":{\"signal\":\"SKIP_NAVIGATION\"}}]}}}}}},\"microformat\":{\"microformatDataRenderer\":{\"urlCanonical\":\"https://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"title\":\":melondeagua: archive\",\"description\":\"yo, soy, el melon de agua\",\"thumbnail\":{\"thumbnails\":[{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s200-c-k-c0x00ffffff-no-rj?days_since_epoch=20603\",\"width\":200,\"height\":200}]},\"siteName\":\"YouTube\",\"appName\":\"YouTube\",\"androidPackage\":\"com.google.android.youtube\",\"iosAppStoreId\":\"544007664\",\"iosAppArguments\":\"https://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"ogType\":\"yt-fb-app:channel\",\"urlApplinksWeb\":\"https://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=applinks\",\"urlApplinksIos\":\"vnd.youtube://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=applinks\",\"urlApplinksAndroid\":\"vnd.youtube://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=applinks\",\"urlTwitterIos\":\"vnd.youtube://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=twitter-deep-link\",\"urlTwitterAndroid\":\"vnd.youtube://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=twitter-deep-link\",\"twitterCardType\":\"summary\",\"twitterSiteHandle\":\"@YouTube\",\"schemaDotOrgType\":\"http://schema.org/http://schema.org/YoutubeChannelV2\",\"noindex\":false,\"unlisted\":false,\"familySafe\":true,\"availableCountries\":[\"MY\",\"DM\",\"US\",\"GB\",\"GR\",\"NU\",\"MV\",\"GI\",\"SK\",\"ZW\",\"FM\",\"UM\",\"MZ\",\"ES\",\"IL\",\"CA\",\"HR\",\"DK\",\"MH\",\"PR\",\"JO\",\"YT\",\"GW\",\"CG\",\"GT\",\"BM\",\"IM\",\"ST\",\"WF\",\"AD\",\"KM\",\"DZ\",\"BD\",\"GU\",\"JM\",\"BG\",\"DO\",\"SY\",\"AX\",\"PL\",\"PS\",\"OM\",\"SH\",\"ET\",\"IT\",\"CW\",\"KW\",\"AO\",\"BV\",\"LR\",\"LS\",\"BW\",\"TO\",\"FK\",\"CL\",\"SZ\",\"CR\",\"LA\",\"SE\",\"GH\",\"BO\",\"GN\",\"SD\",\"HM\",\"GD\",\"RO\",\"TW\",\"SM\",\"CN\",\"AS\",\"VN\",\"CI\",\"NI\",\"DE\",\"CV\",\"VC\",\"CC\",\"BQ\",\"MP\",\"AG\",\"KR\",\"BH\",\"VI\",\"LT\",\"BN\",\"VU\",\"CX\",\"IS\",\"BA\",\"AT\",\"GS\",\"KP\",\"FO\",\"SO\",\"GF\",\"SX\",\"NG\",\"TD\",\"LK\",\"MS\",\"AZ\",\"YE\",\"IR\",\"MK\",\"MN\",\"RE\",\"SG\",\"KE\",\"KI\",\"CD\",\"CY\",\"AW\",\"KN\",\"NC\",\"PG\",\"AM\",\"KH\",\"PE\",\"PT\",\"TF\",\"EH\",\"EC\",\"GL\",\"UA\",\"MT\",\"ME\",\"TL\",\"BY\",\"BE\",\"HT\",\"LY\",\"MW\",\"SI\",\"AQ\",\"MF\",\"TN\",\"TK\",\"MA\",\"MD\",\"LI\",\"HN\",\"AR\",\"PN\",\"SL\",\"NO\",\"GM\",\"PA\",\"SN\",\"IN\",\"PH\",\"SR\",\"IO\",\"HK\",\"AF\",\"TV\",\"SA\",\"MC\",\"FI\",\"EE\",\"BL\",\"LV\",\"VG\",\"MQ\",\"NR\",\"QA\",\"AL\",\"NZ\",\"BR\",\"CK\",\"TJ\",\"VA\",\"IQ\",\"UZ\",\"GQ\",\"AI\",\"MG\",\"ML\",\"GY\",\"RW\",\"KY\",\"MO\",\"TZ\",\"LU\",\"HU\",\"ID\",\"MX\",\"PW\",\"SV\",\"GG\",\"NL\",\"CH\",\"TC\",\"PF\",\"BI\",\"ZA\",\"MR\",\"JP\",\"BB\",\"ER\",\"LC\",\"BF\",\"TH\",\"TM\",\"JE\",\"MU\",\"RU\",\"SJ\",\"BT\",\"EG\",\"NA\",\"NF\",\"PM\",\"SS\",\"FR\",\"NE\",\"DJ\",\"BZ\",\"UG\",\"NP\",\"PK\",\"CM\",\"TR\",\"KZ\",\"IE\",\"MM\",\"AU\",\"PY\",\"TT\",\"KG\",\"VE\",\"SC\",\"GE\",\"SB\",\"BJ\",\"UY\",\"RS\",\"TG\",\"CO\",\"BS\",\"AE\",\"CF\",\"CU\",\"GP\",\"GA\",\"CZ\",\"WS\",\"FJ\",\"LB\",\"ZM\"],\"linkAlternates\":[{\"hrefUrl\":\"https://m.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\"},{\"hrefUrl\":\"android-app://com.google.android.youtube/http/youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\"},{\"hrefUrl\":\"ios-app://544007664/http/youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\"}]}}}', 1780103641, '2026-05-30 00:14:01');
INSERT INTO `app_cache` (`cache_key`, `cache_value`, `expires_at`, `updated_at`) VALUES
('yt_it_fdbb47639653228971cbca9944ef1112', '{\"responseContext\":{\"visitorData\":\"Cgt6Wno0UWVuX3RjOCjJ1-jQBjIoCgJFUxIiEh4SHAsMDg8QERITFBUWFxgZGhscHR4fICEiIyQlJicgFA%3D%3D\",\"serviceTrackingParams\":[{\"service\":\"GFEEDBACK\",\"params\":[{\"key\":\"route\",\"value\":\"channel.featured\"},{\"key\":\"is_owner\",\"value\":\"false\"},{\"key\":\"is_alc_surface\",\"value\":\"false\"},{\"key\":\"browse_id\",\"value\":\"UCCWfUXfPgp3DEBpPem_LQOw\"},{\"key\":\"browse_id_prefix\",\"value\":\"\"},{\"key\":\"logged_in\",\"value\":\"0\"},{\"key\":\"visitor_data\",\"value\":\"Cgt6Wno0UWVuX3RjOCjJ1-jQBjIoCgJFUxIiEh4SHAsMDg8QERITFBUWFxgZGhscHR4fICEiIyQlJicgFA%3D%3D\"}]},{\"service\":\"GOOGLE_HELP\",\"params\":[{\"key\":\"browse_id\",\"value\":\"UCCWfUXfPgp3DEBpPem_LQOw\"},{\"key\":\"browse_id_prefix\",\"value\":\"\"}]},{\"service\":\"CSI\",\"params\":[{\"key\":\"c\",\"value\":\"WEB\"},{\"key\":\"cver\",\"value\":\"2.20240101.05.00\"},{\"key\":\"yt_li\",\"value\":\"0\"},{\"key\":\"GetChannelPage_rid\",\"value\":\"0x1f059e56eba3ad5a\"}]},{\"service\":\"GUIDED_HELP\",\"params\":[{\"key\":\"logged_in\",\"value\":\"0\"}]},{\"service\":\"ECATCHER\",\"params\":[{\"key\":\"client.version\",\"value\":\"2.20250331\"},{\"key\":\"client.name\",\"value\":\"WEB\"}]}],\"maxAgeSeconds\":300,\"mainAppWebResponseContext\":{\"loggedOut\":true,\"trackingParam\":\"k5_fmPxhoXZRGAyUQk9ehYNznDy6HT3JEb7X0kjxp-lrs0DPyUJPqHNImwRMkusEmIBwOcCw59TLtslLKPQGSS\"},\"responseId\":\"IhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"webResponseContextExtensionData\":{\"webResponseContextPreloadData\":{\"preloadMessageNames\":[\"pageHeaderRenderer\",\"pageHeaderViewModel\",\"dynamicTextViewModel\",\"decoratedAvatarViewModel\",\"avatarViewModel\",\"contentMetadataViewModel\",\"flexibleActionsViewModel\",\"buttonViewModel\",\"modalWithTitleAndButtonRenderer\",\"buttonRenderer\",\"descriptionPreviewViewModel\",\"engagementPanelSectionListRenderer\",\"engagementPanelTitleHeaderRenderer\",\"sectionListRenderer\",\"itemSectionRenderer\",\"continuationItemRenderer\",\"channelMetadataRenderer\",\"twoColumnBrowseResultsRenderer\",\"tabRenderer\",\"channelOwnerEmptyStateRenderer\",\"expandableTabRenderer\",\"desktopTopbarRenderer\",\"topbarLogoRenderer\",\"fusionSearchboxRenderer\",\"dialogViewModel\",\"dialogHeaderViewModel\",\"panelFooterViewModel\",\"basicContentViewModel\",\"topbarMenuButtonRenderer\",\"multiPageMenuRenderer\",\"hotkeyDialogRenderer\",\"hotkeyDialogSectionRenderer\",\"hotkeyDialogSectionOptionRenderer\",\"microformatDataRenderer\"]},\"hasDecorated\":true}},\"contents\":{\"twoColumnBrowseResultsRenderer\":{\"tabs\":[{\"tabRenderer\":{\"endpoint\":{\"clickTrackingParams\":\"CBoQ8JMBGAUiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/featured\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EghmZWF0dXJlZPIGBAoCMgA%3D\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"title\":\"Home\",\"selected\":true,\"content\":{\"sectionListRenderer\":{\"contents\":[{\"channelOwnerEmptyStateRenderer\":{\"illustration\":{\"thumbnails\":[{\"url\":\"https://www.gstatic.com/youtube/img/channels/mobile/empty_channel/light_800x800.png\"}]},\"description\":{\"simpleText\":\"This channel doesn\'t have any content\"}}}],\"trackingParams\":\"CBsQui8iEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"disablePullToRefresh\":true}},\"trackingParams\":\"CBoQ8JMBGAUiEwjJv_C03d-UAxU17UkHHQ4TIVk=\"}},{\"tabRenderer\":{\"endpoint\":{\"clickTrackingParams\":\"CBkQ8JMBGAYiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/playlists\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EglwbGF5bGlzdHPyBgQKAkIA\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"title\":\"Playlists\",\"trackingParams\":\"CBkQ8JMBGAYiEwjJv_C03d-UAxU17UkHHQ4TIVk=\"}},{\"expandableTabRenderer\":{\"endpoint\":{\"clickTrackingParams\":\"CAAQhGciEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive/search\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EgZzZWFyY2jyBgQKAloA\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"title\":\"Search\",\"selected\":false}}]}},\"header\":{\"pageHeaderRenderer\":{\"pageTitle\":\":melondeagua: archive\",\"content\":{\"pageHeaderViewModel\":{\"title\":{\"dynamicTextViewModel\":{\"text\":{\"content\":\":melondeagua: archive\"},\"maxLines\":2,\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CBgQj-QKIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"visibility\":{\"types\":\"12\"}}}}}},\"image\":{\"decoratedAvatarViewModel\":{\"avatar\":{\"avatarViewModel\":{\"image\":{\"sources\":[{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s72-c-k-c0x00ffffff-no-rj\",\"width\":72,\"height\":72},{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s120-c-k-c0x00ffffff-no-rj\",\"width\":120,\"height\":120},{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s160-c-k-c0x00ffffff-no-rj\",\"width\":160,\"height\":160}],\"processor\":{\"borderImageProcessor\":{\"circular\":true}}},\"avatarImageSize\":\"AVATAR_SIZE_XL\",\"loggingDirectives\":{\"trackingParams\":\"CBcQ6OENIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"visibility\":{\"types\":\"12\"}}}}}},\"metadata\":{\"contentMetadataViewModel\":{\"metadataRows\":[{\"metadataParts\":[{\"text\":{\"content\":\"@melondeaguaarchive\",\"styleRuns\":[{\"weightLabel\":\"FONT_WEIGHT_MEDIUM\",\"styleRunExtensions\":{\"styleRunColorMapExtension\":{\"colorMap\":[{\"key\":\"USER_INTERFACE_THEME_DARK\",\"value\":4294967295},{\"key\":\"USER_INTERFACE_THEME_LIGHT\",\"value\":4279440147}]}}}]},\"enableTruncation\":true}]},{\"metadataParts\":[{\"text\":{\"content\":\"16 subscribers\"},\"accessibilityLabel\":\"16 subscribers\"}]}],\"delimiter\":\"•\",\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CBYQ9eQKIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"visibility\":{\"types\":\"12\"}}}}}},\"actions\":{\"flexibleActionsViewModel\":{\"actionsRows\":[{\"actions\":[{\"buttonViewModel\":{\"title\":\"Subscribe\",\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CA8Qot8KIhMIyb_wtN3flAMVNe1JBx0OEyFZygEEC_oFiw==\",\"commandMetadata\":{\"webCommandMetadata\":{\"ignoreNavigation\":true}},\"modalEndpoint\":{\"modal\":{\"modalWithTitleAndButtonRenderer\":{\"title\":{\"simpleText\":\"Want to subscribe to this channel?\"},\"content\":{\"simpleText\":\"Sign in to subscribe to this channel.\"},\"button\":{\"buttonRenderer\":{\"style\":\"STYLE_MONO_FILLED\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"text\":{\"simpleText\":\"Sign in\"},\"navigationEndpoint\":{\"clickTrackingParams\":\"CBUQ_YYEIhMIyb_wtN3flAMVNe1JBx0OEyFZMglzdWJzY3JpYmXKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"https://accounts.google.com/ServiceLogin?service=youtube&uilel=3&passive=true&continue=https%3A%2F%2Fwww.youtube.com%2Fsignin%3Faction_handle_signin%3Dtrue%26app%3Ddesktop%26hl%3Den%26next%3D%252F%2540melondeaguaarchive%26continue_action%3DQUFFLUhqa3R4c0lsQXJGTTN5TjZ2VXdtcW1VTlZfRy1Kd3xBQ3Jtc0trNjItdlgzRnRXMm42YWEyY2k5MWJDNmpneVo1RFFuUVJScWxLVmpJRm5hSzJlTFA3aWtTdjczeXMzV0ZWZDE0a25rYVpxVFp2X09NcW55MFBmdVdzUlhyMC05V2NpVjNXNFV6M2dWZ0VBbWJkamlSTkZWN01CRFB4QlBjN1hVRzI0RmMzOWlaTWdqc1VqbnRJbHVjOGdnLXZ0aENOZDlPQnRabDZVa09LUmkxZWRsOXV0dExza2lMaDVHRHBsZ1dtX0VBSmI&hl=en&ec=66429\",\"webPageType\":\"WEB_PAGE_TYPE_UNKNOWN\",\"rootVe\":83769}},\"signInEndpoint\":{\"nextEndpoint\":{\"clickTrackingParams\":\"CBUQ_YYEIhMIyb_wtN3flAMVNe1JBx0OEyFZygEEC_oFiw==\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/@melondeaguaarchive\",\"webPageType\":\"WEB_PAGE_TYPE_CHANNEL\",\"rootVe\":3611,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"params\":\"EgA%3D\",\"canonicalBaseUrl\":\"/@melondeaguaarchive\"}},\"continueAction\":\"QUFFLUhqa3R4c0lsQXJGTTN5TjZ2VXdtcW1VTlZfRy1Kd3xBQ3Jtc0trNjItdlgzRnRXMm42YWEyY2k5MWJDNmpneVo1RFFuUVJScWxLVmpJRm5hSzJlTFA3aWtTdjczeXMzV0ZWZDE0a25rYVpxVFp2X09NcW55MFBmdVdzUlhyMC05V2NpVjNXNFV6M2dWZ0VBbWJkamlSTkZWN01CRFB4QlBjN1hVRzI0RmMzOWlaTWdqc1VqbnRJbHVjOGdnLXZ0aENOZDlPQnRabDZVa09LUmkxZWRsOXV0dExza2lMaDVHRHBsZ1dtX0VBSmI\",\"idamTag\":\"66429\"}},\"trackingParams\":\"CBUQ_YYEIhMIyb_wtN3flAMVNe1JBx0OEyFZ\"}}}}}}},\"accessibilityText\":\"Subscribe\",\"style\":\"BUTTON_VIEW_MODEL_STYLE_MONO\",\"trackingParams\":\"CA8Qot8KIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"isFullWidth\":false,\"type\":\"BUTTON_VIEW_MODEL_TYPE_FILLED\",\"buttonSize\":\"BUTTON_VIEW_MODEL_SIZE_DEFAULT\",\"state\":\"BUTTON_VIEW_MODEL_STATE_ACTIVE\"}}]}],\"justifyContent\":\"FLEXIBLE_ACTIONS_JUSTIFY_CONTENT_START\",\"minimumRowHeight\":44,\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CA8Qot8KIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"visibility\":{\"types\":\"12\"},\"clientVeSpec\":{\"uiType\":184974,\"veCounter\":840578421}}}}}},\"description\":{\"descriptionPreviewViewModel\":{\"description\":{\"content\":\"yo, soy, el melon de agua \"},\"maxLines\":2,\"truncationText\":{\"content\":\"...more\",\"styleRuns\":[{\"startIndex\":0,\"length\":7,\"weight\":500}]},\"alwaysShowTruncationText\":true,\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CBAQr_4KIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"visibility\":{\"types\":\"12\"}}},\"accessibilityContext\":{\"label\":\"Description. yo, soy, el melon de agua...tap for more.\"},\"commandContext\":{\"onTap\":{\"innertubeCommand\":{\"clickTrackingParams\":\"CBAQr_4KIhMIyb_wtN3flAMVNe1JBx0OEyFZygEEC_oFiw==\",\"showEngagementPanelEndpoint\":{\"engagementPanel\":{\"engagementPanelSectionListRenderer\":{\"header\":{\"engagementPanelTitleHeaderRenderer\":{\"title\":{\"simpleText\":\":melondeagua: archive\"},\"visibilityButton\":{\"buttonRenderer\":{\"style\":\"STYLE_DEFAULT\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"icon\":{\"iconType\":\"CLOSE\"},\"accessibility\":{\"label\":\"Close\"},\"trackingParams\":\"CBQQ8FsiEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"accessibilityData\":{\"accessibilityData\":{\"label\":\"Close\"}},\"command\":{\"clickTrackingParams\":\"CBQQ8FsiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"changeEngagementPanelVisibilityAction\":{\"targetId\":\"6b93261d-0000-230e-87e5-582429be3838\",\"visibility\":\"ENGAGEMENT_PANEL_VISIBILITY_HIDDEN\"}}}},\"trackingParams\":\"CBEQ040EIhMIyb_wtN3flAMVNe1JBx0OEyFZ\"}},\"content\":{\"sectionListRenderer\":{\"contents\":[{\"itemSectionRenderer\":{\"contents\":[{\"continuationItemRenderer\":{\"trigger\":\"CONTINUATION_TRIGGER_ON_ITEM_SHOWN\",\"continuationEndpoint\":{\"clickTrackingParams\":\"CBMQuy8YACITCMm_8LTd35QDFTXtSQcdDhMhWcoBBAv6BYs=\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true,\"apiUrl\":\"/youtubei/v1/browse\"}},\"continuationCommand\":{\"token\":\"4qmFsgJgEhhVQ0NXZlVYZlBncDNERUJwUGVtX0xRT3caRDhnWXJHaW1hQVNZS0pEWmlPVE15TmpGbExUQXdNREF0TWpNd1pTMDROMlUxTFRVNE1qUXlPV0psTXpnek9BJTNEJTNE\",\"request\":\"CONTINUATION_REQUEST_TYPE_BROWSE\"}}}}],\"trackingParams\":\"CBMQuy8YACITCMm_8LTd35QDFTXtSQcdDhMhWQ==\",\"sectionIdentifier\":\"6b93261e-0000-230e-87e5-582429be3838\",\"targetId\":\"6b93261e-0000-230e-87e5-582429be3838\"}}],\"trackingParams\":\"CBIQui8iEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"scrollPaneStyle\":{\"scrollable\":true}}},\"targetId\":\"6b93261d-0000-230e-87e5-582429be3838\",\"identifier\":{\"surface\":\"ENGAGEMENT_PANEL_SURFACE_BROWSE\",\"tag\":\"6b93261d-0000-230e-87e5-582429be3838\"}}},\"identifier\":{\"surface\":\"ENGAGEMENT_PANEL_SURFACE_BROWSE\",\"tag\":\"6b93261d-0000-230e-87e5-582429be3838\"},\"engagementPanelPresentationConfigs\":{\"engagementPanelPopupPresentationConfig\":{\"popupType\":\"PANEL_POPUP_TYPE_DIALOG\"}}}}}}}}},\"rendererContext\":{\"loggingContext\":{\"loggingDirectives\":{\"trackingParams\":\"CA8Qot8KIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"visibility\":{\"types\":\"12\"}}}}}}}},\"metadata\":{\"channelMetadataRenderer\":{\"title\":\":melondeagua: archive\",\"description\":\"yo, soy, el melon de agua\",\"rssUrl\":\"https://www.youtube.com/feeds/videos.xml?channel_id=UCCWfUXfPgp3DEBpPem_LQOw\",\"externalId\":\"UCCWfUXfPgp3DEBpPem_LQOw\",\"keywords\":\"\",\"ownerUrls\":[\"http://www.youtube.com/@melondeaguaarchive\"],\"avatar\":{\"thumbnails\":[{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s900-c-k-c0x00ffffff-no-rj\",\"width\":900,\"height\":900}]},\"channelUrl\":\"https://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"isFamilySafe\":true,\"availableCountryCodes\":[\"MG\",\"DZ\",\"WS\",\"PH\",\"EC\",\"SD\",\"CU\",\"DO\",\"IR\",\"TC\",\"LK\",\"NA\",\"NE\",\"NF\",\"HM\",\"PL\",\"AS\",\"LV\",\"VE\",\"CD\",\"PY\",\"EG\",\"MU\",\"TW\",\"VG\",\"ZM\",\"GT\",\"ZW\",\"MO\",\"CX\",\"GQ\",\"AW\",\"BO\",\"GP\",\"NL\",\"TM\",\"CO\",\"AI\",\"FK\",\"GF\",\"KE\",\"BI\",\"BJ\",\"KY\",\"SM\",\"PK\",\"MV\",\"TT\",\"AZ\",\"LI\",\"GL\",\"SS\",\"MH\",\"PF\",\"AO\",\"HK\",\"CY\",\"SX\",\"CW\",\"AR\",\"SA\",\"IM\",\"SI\",\"NG\",\"DK\",\"AT\",\"MA\",\"CG\",\"FO\",\"GB\",\"FI\",\"LY\",\"PN\",\"BA\",\"DM\",\"KW\",\"GS\",\"CM\",\"CN\",\"GM\",\"TO\",\"AL\",\"LS\",\"QA\",\"HT\",\"JP\",\"HR\",\"UG\",\"TF\",\"BW\",\"JE\",\"AE\",\"JO\",\"LC\",\"YE\",\"GN\",\"RW\",\"BQ\",\"KP\",\"KH\",\"BR\",\"IO\",\"TR\",\"LU\",\"UY\",\"CH\",\"RS\",\"MD\",\"SB\",\"BT\",\"FJ\",\"AU\",\"SG\",\"NI\",\"GA\",\"MN\",\"BV\",\"SN\",\"TL\",\"AF\",\"MQ\",\"ES\",\"MR\",\"US\",\"RE\",\"HN\",\"FR\",\"GE\",\"GG\",\"JM\",\"TZ\",\"PS\",\"ZA\",\"HU\",\"OM\",\"GW\",\"KN\",\"KZ\",\"LR\",\"TN\",\"AQ\",\"BM\",\"BS\",\"AG\",\"SL\",\"IL\",\"DJ\",\"BB\",\"MM\",\"NO\",\"NP\",\"RO\",\"MT\",\"SZ\",\"TJ\",\"TK\",\"MZ\",\"BH\",\"SJ\",\"SK\",\"ID\",\"KR\",\"SR\",\"NZ\",\"TD\",\"MC\",\"GY\",\"VN\",\"CK\",\"IT\",\"BZ\",\"BD\",\"FM\",\"SV\",\"VA\",\"PT\",\"WF\",\"EE\",\"IQ\",\"SY\",\"TG\",\"IE\",\"AD\",\"AM\",\"SC\",\"TV\",\"UM\",\"AX\",\"BY\",\"CI\",\"GH\",\"BF\",\"UZ\",\"BG\",\"CZ\",\"MY\",\"RU\",\"GU\",\"CV\",\"BL\",\"BE\",\"SH\",\"CR\",\"VC\",\"ME\",\"SO\",\"MW\",\"CF\",\"CL\",\"IS\",\"MS\",\"PG\",\"PR\",\"TH\",\"MK\",\"MF\",\"ET\",\"PM\",\"IN\",\"MP\",\"ST\",\"GD\",\"GI\",\"BN\",\"PW\",\"UA\",\"NU\",\"DE\",\"YT\",\"KI\",\"LB\",\"SE\",\"KG\",\"NC\",\"NR\",\"LT\",\"ML\",\"VI\",\"LA\",\"GR\",\"CA\",\"EH\",\"PE\",\"MX\",\"VU\",\"KM\",\"PA\",\"ER\",\"CC\"],\"androidDeepLink\":\"android-app://com.google.android.youtube/http/www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"androidAppindexingLink\":\"android-app://com.google.android.youtube/http/www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"iosAppindexingLink\":\"ios-app://544007664/vnd.youtube/www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"vanityChannelUrl\":\"http://www.youtube.com/@melondeaguaarchive\"}},\"trackingParams\":\"CAAQhGciEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"topbar\":{\"desktopTopbarRenderer\":{\"logo\":{\"topbarLogoRenderer\":{\"iconImage\":{\"iconType\":\"YOUTUBE_LOGO\"},\"tooltipText\":{\"runs\":[{\"text\":\"YouTube Home\"}]},\"endpoint\":{\"clickTrackingParams\":\"CA4QsV4iEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/\",\"webPageType\":\"WEB_PAGE_TYPE_BROWSE\",\"rootVe\":3854,\"apiUrl\":\"/youtubei/v1/browse\"}},\"browseEndpoint\":{\"browseId\":\"FEwhat_to_watch\"}},\"trackingParams\":\"CA4QsV4iEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"overrideEntityKey\":\"EgZ0b3BiYXIg9QEoAQ%3D%3D\"}},\"searchbox\":{\"fusionSearchboxRenderer\":{\"icon\":{\"iconType\":\"SEARCH\"},\"placeholderText\":{\"runs\":[{\"text\":\"Search\"}]},\"config\":{\"webSearchboxConfig\":{\"requestLanguage\":\"en\",\"requestDomain\":\"us\",\"hasOnscreenKeyboard\":false,\"focusSearchbox\":true}},\"trackingParams\":\"CAoQ7VAiEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"searchEndpoint\":{\"clickTrackingParams\":\"CAoQ7VAiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"/results?search_query=\",\"webPageType\":\"WEB_PAGE_TYPE_SEARCH\",\"rootVe\":4724}},\"searchEndpoint\":{\"query\":\"\"}},\"clearButton\":{\"buttonRenderer\":{\"style\":\"STYLE_DEFAULT\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"icon\":{\"iconType\":\"CLOSE\"},\"trackingParams\":\"CA0Q8FsiEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"accessibilityData\":{\"accessibilityData\":{\"label\":\"Clear search query\"}}}},\"showImageSourceDialog\":{\"clickTrackingParams\":\"CAoQ7VAiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"showDialogCommand\":{\"panelLoadingStrategy\":{\"inlineContent\":{\"dialogViewModel\":{\"header\":{\"dialogHeaderViewModel\":{\"headline\":{\"content\":\"Image source\"}}},\"footer\":{\"panelFooterViewModel\":{\"primaryButton\":{\"buttonViewModel\":{\"title\":\"Visit source\",\"style\":\"BUTTON_VIEW_MODEL_STYLE_MONO\",\"trackingParams\":\"CAwQ8FsiEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"isFullWidth\":true,\"type\":\"BUTTON_VIEW_MODEL_TYPE_FILLED\"}},\"secondaryButton\":{\"buttonViewModel\":{\"title\":\"Cancel\",\"style\":\"BUTTON_VIEW_MODEL_STYLE_MONO\",\"trackingParams\":\"CAsQ8FsiEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"isFullWidth\":true,\"type\":\"BUTTON_VIEW_MODEL_TYPE_TONAL\"}},\"shouldHideDivider\":true}},\"content\":{\"basicContentViewModel\":{\"paragraphs\":[{\"text\":{\"content\":\"Visit image source website?\"}}]}}}}}}}}},\"trackingParams\":\"CAEQq6wBIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"topbarButtons\":[{\"topbarMenuButtonRenderer\":{\"icon\":{\"iconType\":\"MORE_VERT\"},\"menuRequest\":{\"clickTrackingParams\":\"CAgQ_qsBGAAiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true,\"apiUrl\":\"/youtubei/v1/account/account_menu\"}},\"signalServiceEndpoint\":{\"signal\":\"GET_ACCOUNT_MENU\",\"actions\":[{\"clickTrackingParams\":\"CAgQ_qsBGAAiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"openPopupAction\":{\"popup\":{\"multiPageMenuRenderer\":{\"trackingParams\":\"CAkQ_6sBIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"style\":\"MULTI_PAGE_MENU_STYLE_TYPE_SYSTEM\",\"showLoadingSpinner\":true}},\"popupType\":\"DROPDOWN\",\"beReused\":true}}]}},\"trackingParams\":\"CAgQ_qsBGAAiEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"accessibility\":{\"accessibilityData\":{\"label\":\"Settings\"}},\"tooltip\":\"Settings\",\"style\":\"STYLE_DEFAULT\"}},{\"buttonRenderer\":{\"style\":\"STYLE_SUGGESTIVE\",\"size\":\"SIZE_SMALL\",\"text\":{\"runs\":[{\"text\":\"Sign in\"}]},\"icon\":{\"iconType\":\"AVATAR_LOGGED_OUT\"},\"navigationEndpoint\":{\"clickTrackingParams\":\"CAcQ1IAEGAEiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"url\":\"https://accounts.google.com/ServiceLogin?service=youtube&uilel=3&passive=true&continue=https%3A%2F%2Fwww.youtube.com%2Fsignin%3Faction_handle_signin%3Dtrue%26app%3Ddesktop%26hl%3Den%26next%3Dhttps%253A%252F%252Fwww.youtube.com%252Fyoutubei%252Fv1%252Fbrowse%253Fkey%253DAIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8%2526prettyPrint%253Dfalse&hl=en&ec=65620\",\"webPageType\":\"WEB_PAGE_TYPE_UNKNOWN\",\"rootVe\":83769}},\"signInEndpoint\":{\"idamTag\":\"65620\"}},\"trackingParams\":\"CAcQ1IAEGAEiEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"targetId\":\"topbar-signin\"}}],\"hotkeyDialog\":{\"hotkeyDialogRenderer\":{\"title\":{\"runs\":[{\"text\":\"Keyboard shortcuts\"}]},\"sections\":[{\"hotkeyDialogSectionRenderer\":{\"title\":{\"runs\":[{\"text\":\"Playback\"}]},\"options\":[{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle play/pause\"}]},\"hotkey\":\"k\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rewind 10 seconds\"}]},\"hotkey\":\"j\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Fast forward 10 seconds\"}]},\"hotkey\":\"l\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Previous video\"}]},\"hotkey\":\"P (SHIFT+p)\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Next video\"}]},\"hotkey\":\"N (SHIFT+n)\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Previous frame (while paused)\"}]},\"hotkey\":\",\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Comma\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Next frame (while paused)\"}]},\"hotkey\":\".\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Period\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Decrease playback rate\"}]},\"hotkey\":\"\\u003c (SHIFT+,)\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Less than or SHIFT + comma\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Increase playback rate\"}]},\"hotkey\":\"\\u003e (SHIFT+.)\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Greater than or SHIFT + period\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Seek to specific point in the video (7 advances to 70% of duration)\"}]},\"hotkey\":\"0..9\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Seek to previous chapter\"}]},\"hotkey\":\"CONTROL + ←\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Seek to next chapter\"}]},\"hotkey\":\"CONTROL + →\"}}]}},{\"hotkeyDialogSectionRenderer\":{\"title\":{\"runs\":[{\"text\":\"General\"}]},\"options\":[{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle full screen\"}]},\"hotkey\":\"f\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle theater mode\"}]},\"hotkey\":\"t\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle miniplayer\"}]},\"hotkey\":\"i\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Close miniplayer or current dialog\"}]},\"hotkey\":\"ESCAPE\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Toggle mute\"}]},\"hotkey\":\"m\"}}]}},{\"hotkeyDialogSectionRenderer\":{\"title\":{\"runs\":[{\"text\":\"Subtitles and closed captions\"}]},\"options\":[{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"If the video supports captions, toggle captions ON/OFF\"}]},\"hotkey\":\"c\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rotate through different text opacity levels\"}]},\"hotkey\":\"o\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rotate through different window opacity levels\"}]},\"hotkey\":\"w\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rotate through font sizes (increasing)\"}]},\"hotkey\":\"+\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Rotate through font sizes (decreasing)\"}]},\"hotkey\":\"-\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Minus\"}}}}]}},{\"hotkeyDialogSectionRenderer\":{\"title\":{\"runs\":[{\"text\":\"Spherical Videos\"}]},\"options\":[{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Pan up\"}]},\"hotkey\":\"w\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Pan left\"}]},\"hotkey\":\"a\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Pan down\"}]},\"hotkey\":\"s\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Pan right\"}]},\"hotkey\":\"d\"}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Zoom in\"}]},\"hotkey\":\"+ on numpad or ]\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Plus on number pad or right bracket\"}}}},{\"hotkeyDialogSectionOptionRenderer\":{\"label\":{\"runs\":[{\"text\":\"Zoom out\"}]},\"hotkey\":\"- on numpad or [\",\"hotkeyAccessibilityLabel\":{\"accessibilityData\":{\"label\":\"Minus on number pad or left bracket\"}}}}]}}],\"dismissButton\":{\"buttonRenderer\":{\"style\":\"STYLE_BLUE_TEXT\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"text\":{\"runs\":[{\"text\":\"Dismiss\"}]},\"trackingParams\":\"CAYQ8FsiEwjJv_C03d-UAxU17UkHHQ4TIVk=\"}},\"trackingParams\":\"CAUQteYDIhMIyb_wtN3flAMVNe1JBx0OEyFZ\"}},\"backButton\":{\"buttonRenderer\":{\"trackingParams\":\"CAQQvIYDIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"command\":{\"clickTrackingParams\":\"CAQQvIYDIhMIyb_wtN3flAMVNe1JBx0OEyFZygEEC_oFiw==\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true}},\"signalServiceEndpoint\":{\"signal\":\"CLIENT_SIGNAL\",\"actions\":[{\"clickTrackingParams\":\"CAQQvIYDIhMIyb_wtN3flAMVNe1JBx0OEyFZygEEC_oFiw==\",\"signalAction\":{\"signal\":\"HISTORY_BACK\"}}]}}}},\"forwardButton\":{\"buttonRenderer\":{\"trackingParams\":\"CAMQvYYDIhMIyb_wtN3flAMVNe1JBx0OEyFZ\",\"command\":{\"clickTrackingParams\":\"CAMQvYYDIhMIyb_wtN3flAMVNe1JBx0OEyFZygEEC_oFiw==\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true}},\"signalServiceEndpoint\":{\"signal\":\"CLIENT_SIGNAL\",\"actions\":[{\"clickTrackingParams\":\"CAMQvYYDIhMIyb_wtN3flAMVNe1JBx0OEyFZygEEC_oFiw==\",\"signalAction\":{\"signal\":\"HISTORY_FORWARD\"}}]}}}},\"a11ySkipNavigationButton\":{\"buttonRenderer\":{\"style\":\"STYLE_DEFAULT\",\"size\":\"SIZE_DEFAULT\",\"isDisabled\":false,\"text\":{\"runs\":[{\"text\":\"Skip navigation\"}]},\"trackingParams\":\"CAIQ8FsiEwjJv_C03d-UAxU17UkHHQ4TIVk=\",\"command\":{\"clickTrackingParams\":\"CAIQ8FsiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"commandMetadata\":{\"webCommandMetadata\":{\"sendPost\":true}},\"signalServiceEndpoint\":{\"signal\":\"CLIENT_SIGNAL\",\"actions\":[{\"clickTrackingParams\":\"CAIQ8FsiEwjJv_C03d-UAxU17UkHHQ4TIVnKAQQL-gWL\",\"signalAction\":{\"signal\":\"SKIP_NAVIGATION\"}}]}}}}}},\"microformat\":{\"microformatDataRenderer\":{\"urlCanonical\":\"https://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"title\":\":melondeagua: archive\",\"description\":\"yo, soy, el melon de agua\",\"thumbnail\":{\"thumbnails\":[{\"url\":\"https://yt3.googleusercontent.com/FBj902gWlfjjaytmqa2nAZXhZIPaTbXhgFsxUO33u51dm2Ae7Ig1195Nh8RPnz3UU5F0oKH6LA=s200-c-k-c0x00ffffff-no-rj?days_since_epoch=20603\",\"width\":200,\"height\":200}]},\"siteName\":\"YouTube\",\"appName\":\"YouTube\",\"androidPackage\":\"com.google.android.youtube\",\"iosAppStoreId\":\"544007664\",\"iosAppArguments\":\"https://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\",\"ogType\":\"yt-fb-app:channel\",\"urlApplinksWeb\":\"https://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=applinks\",\"urlApplinksIos\":\"vnd.youtube://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=applinks\",\"urlApplinksAndroid\":\"vnd.youtube://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=applinks\",\"urlTwitterIos\":\"vnd.youtube://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=twitter-deep-link\",\"urlTwitterAndroid\":\"vnd.youtube://www.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw?feature=twitter-deep-link\",\"twitterCardType\":\"summary\",\"twitterSiteHandle\":\"@YouTube\",\"schemaDotOrgType\":\"http://schema.org/http://schema.org/YoutubeChannelV2\",\"noindex\":false,\"unlisted\":false,\"familySafe\":true,\"availableCountries\":[\"MG\",\"DZ\",\"WS\",\"PH\",\"EC\",\"SD\",\"CU\",\"DO\",\"IR\",\"TC\",\"LK\",\"NA\",\"NE\",\"NF\",\"HM\",\"PL\",\"AS\",\"LV\",\"VE\",\"CD\",\"PY\",\"EG\",\"MU\",\"TW\",\"VG\",\"ZM\",\"GT\",\"ZW\",\"MO\",\"CX\",\"GQ\",\"AW\",\"BO\",\"GP\",\"NL\",\"TM\",\"CO\",\"AI\",\"FK\",\"GF\",\"KE\",\"BI\",\"BJ\",\"KY\",\"SM\",\"PK\",\"MV\",\"TT\",\"AZ\",\"LI\",\"GL\",\"SS\",\"MH\",\"PF\",\"AO\",\"HK\",\"CY\",\"SX\",\"CW\",\"AR\",\"SA\",\"IM\",\"SI\",\"NG\",\"DK\",\"AT\",\"MA\",\"CG\",\"FO\",\"GB\",\"FI\",\"LY\",\"PN\",\"BA\",\"DM\",\"KW\",\"GS\",\"CM\",\"CN\",\"GM\",\"TO\",\"AL\",\"LS\",\"QA\",\"HT\",\"JP\",\"HR\",\"UG\",\"TF\",\"BW\",\"JE\",\"AE\",\"JO\",\"LC\",\"YE\",\"GN\",\"RW\",\"BQ\",\"KP\",\"KH\",\"BR\",\"IO\",\"TR\",\"LU\",\"UY\",\"CH\",\"RS\",\"MD\",\"SB\",\"BT\",\"FJ\",\"AU\",\"SG\",\"NI\",\"GA\",\"MN\",\"BV\",\"SN\",\"TL\",\"AF\",\"MQ\",\"ES\",\"MR\",\"US\",\"RE\",\"HN\",\"FR\",\"GE\",\"GG\",\"JM\",\"TZ\",\"PS\",\"ZA\",\"HU\",\"OM\",\"GW\",\"KN\",\"KZ\",\"LR\",\"TN\",\"AQ\",\"BM\",\"BS\",\"AG\",\"SL\",\"IL\",\"DJ\",\"BB\",\"MM\",\"NO\",\"NP\",\"RO\",\"MT\",\"SZ\",\"TJ\",\"TK\",\"MZ\",\"BH\",\"SJ\",\"SK\",\"ID\",\"KR\",\"SR\",\"NZ\",\"TD\",\"MC\",\"GY\",\"VN\",\"CK\",\"IT\",\"BZ\",\"BD\",\"FM\",\"SV\",\"VA\",\"PT\",\"WF\",\"EE\",\"IQ\",\"SY\",\"TG\",\"IE\",\"AD\",\"AM\",\"SC\",\"TV\",\"UM\",\"AX\",\"BY\",\"CI\",\"GH\",\"BF\",\"UZ\",\"BG\",\"CZ\",\"MY\",\"RU\",\"GU\",\"CV\",\"BL\",\"BE\",\"SH\",\"CR\",\"VC\",\"ME\",\"SO\",\"MW\",\"CF\",\"CL\",\"IS\",\"MS\",\"PG\",\"PR\",\"TH\",\"MK\",\"MF\",\"ET\",\"PM\",\"IN\",\"MP\",\"ST\",\"GD\",\"GI\",\"BN\",\"PW\",\"UA\",\"NU\",\"DE\",\"YT\",\"KI\",\"LB\",\"SE\",\"KG\",\"NC\",\"NR\",\"LT\",\"ML\",\"VI\",\"LA\",\"GR\",\"CA\",\"EH\",\"PE\",\"MX\",\"VU\",\"KM\",\"PA\",\"ER\",\"CC\"],\"linkAlternates\":[{\"hrefUrl\":\"https://m.youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\"},{\"hrefUrl\":\"android-app://com.google.android.youtube/http/youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\"},{\"hrefUrl\":\"ios-app://544007664/http/youtube.com/channel/UCCWfUXfPgp3DEBpPem_LQOw\"}]}}}', 1780103641, '2026-05-30 00:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `user_a` int(11) NOT NULL,
  `user_b` int(11) NOT NULL,
  `last_seen_a` timestamp NULL DEFAULT NULL,
  `last_seen_b` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `user_a`, `user_b`, `last_seen_a`, `last_seen_b`) VALUES
(1, 1, 2, '2026-05-26 19:19:20', '2026-05-26 16:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `desktop_folders`
--

CREATE TABLE `desktop_folders` (
  `id` varchar(60) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `pos_left` int(11) NOT NULL,
  `pos_top` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `desktop_folders`
--

INSERT INTO `desktop_folders` (`id`, `user_id`, `name`, `pos_left`, `pos_top`, `created_at`) VALUES
('fld-mpn16xu7ekn', 1, 'asdsads', 384, 192, '2026-05-26 19:31:34');

-- --------------------------------------------------------

--
-- Table structure for table `desktop_folder_items`
--

CREATE TABLE `desktop_folder_items` (
  `folder_id` varchar(60) NOT NULL,
  `icon_id` varchar(60) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `desktop_icons`
--

CREATE TABLE `desktop_icons` (
  `user_id` int(11) NOT NULL,
  `icon_id` varchar(60) NOT NULL,
  `pos_left` int(11) NOT NULL,
  `pos_top` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `desktop_icons`
--

INSERT INTO `desktop_icons` (`user_id`, `icon_id`, `pos_left`, `pos_top`, `updated_at`) VALUES
(1, 'archive-icon', 96, 0, '2026-05-29 19:27:11'),
(1, 'calendar-icon', 96, 96, '2026-05-28 18:36:16'),
(1, 'fld-mpn16xu7ekn', 0, 96, '2026-05-28 18:36:35'),
(1, 'profile-icon', 192, 0, '2026-05-28 18:38:17'),
(1, 'temas-icon', 288, 0, '2026-05-29 19:35:10'),
(2, 'companion-icon', 0, 288, '2026-05-30 16:32:44'),
(2, 'dibujo-icon', 96, 288, '2026-05-30 16:32:50'),
(2, 'dnd-icon', 96, 96, '2026-05-30 16:32:46'),
(2, 'galeria-icon', 96, 192, '2026-05-30 16:32:48'),
(2, 'profile-icon', 0, 96, '2026-05-28 18:37:39'),
(2, 'temas-icon', 0, 192, '2026-05-30 16:32:43');

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `follower_id` int(11) NOT NULL,
  `followee_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`follower_id`, `followee_id`, `created_at`) VALUES
(1, 2, '2026-05-26 18:26:08'),
(1, 3, '2026-05-26 18:26:08'),
(2, 1, '2026-05-26 18:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `item_invites`
--

CREATE TABLE `item_invites` (
  `id` bigint(20) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `type` varchar(40) NOT NULL DEFAULT 'invite',
  `item_id` bigint(20) DEFAULT NULL,
  `category` enum('movies','series','books','games','music') NOT NULL,
  `item_title` varchar(200) NOT NULL,
  `item_image` varchar(2000) DEFAULT NULL,
  `item_music_type` enum('song','album') DEFAULT NULL,
  `item_artist` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `list_items`
--

CREATE TABLE `list_items` (
  `id` bigint(20) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `category` enum('movies','series','books','games','music') NOT NULL,
  `title` varchar(200) NOT NULL,
  `image` varchar(2000) DEFAULT NULL,
  `status` enum('pending','in-progress','completed') DEFAULT NULL,
  `music_type` enum('song','album') DEFAULT NULL,
  `artist` varchar(200) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `yt_id` varchar(40) DEFAULT NULL,
  `spotify_id` varchar(40) DEFAULT NULL,
  `yt_playlist_id` varchar(60) DEFAULT NULL,
  `spotify_album_id` varchar(40) DEFAULT NULL,
  `review_stars` decimal(2,1) DEFAULT NULL,
  `review_comment` varchar(1000) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `shared_from` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `list_items`
--

INSERT INTO `list_items` (`id`, `owner_id`, `category`, `title`, `image`, `status`, `music_type`, `artist`, `featured`, `yt_id`, `spotify_id`, `yt_playlist_id`, `spotify_album_id`, `review_stars`, `review_comment`, `reviewed_at`, `shared_from`, `created_at`) VALUES
(5, 2, 'movies', 'bsdds', '', 'completed', NULL, NULL, 0, NULL, NULL, NULL, NULL, 4.5, 'ok', '2026-05-25 16:37:00', 1, '2026-05-26 18:26:08'),
(30, 8, 'series', 'Aída', 'https://images.justwatch.com/poster/306140957/s718/aida.jpg', 'in-progress', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-29 16:19:16'),
(31, 8, 'music', 'Negative Spaces', 'https://i.scdn.co/image/ab67616d00001e02861d5b1470e2d95fe9aeba7c', NULL, 'album', 'Poppy', 1, NULL, NULL, NULL, '0YIOpXQvcbiDNPusSqi5Ew', NULL, NULL, NULL, NULL, '2026-05-29 16:20:30'),
(32, 8, 'music', 'I Disagree', 'https://i.scdn.co/image/ab67616d00001e02281254a268a229556d982056', NULL, 'album', 'Poppy', 1, NULL, NULL, NULL, '6kfpsglFWonoOZlJ8XnMuG', NULL, NULL, NULL, NULL, '2026-05-29 16:20:52'),
(33, 8, 'music', 'bbno$', 'https://i.scdn.co/image/ab67616d00001e029cd48ea54f12cdd1a244ce51', NULL, 'album', 'bbno$', 1, NULL, NULL, NULL, '6NnOcPG7uLUSpJTS83Ra1T', NULL, NULL, NULL, NULL, '2026-05-29 16:21:13'),
(34, 1, 'music', 'Event Horizon (Reach for the Sun and Burn! Burn! Burn!)', 'https://img.youtube.com/vi/stpMtEx5zqc/mqdefault.jpg', NULL, 'song', 'Heaven Pierce Her', 0, 'stpMtEx5zqc', NULL, NULL, NULL, 3.5, 'Bomba', '2026-05-29 22:35:17', NULL, '2026-05-29 22:35:13'),
(35, 1, 'music', 'FIRE!!!', 'https://img.youtube.com/vi/yZkPe7TEuyk/mqdefault.jpg', NULL, 'song', 'Vane Lily', 0, 'yZkPe7TEuyk', NULL, NULL, NULL, 5.0, 'God', '2026-05-29 22:35:28', NULL, '2026-05-29 22:35:24');

-- --------------------------------------------------------

--
-- Table structure for table `list_item_collaborators`
--

CREATE TABLE `list_item_collaborators` (
  `item_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mascotas`
--

CREATE TABLE `mascotas` (
  `user_id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL DEFAULT 'Gabriel',
  `skin` varchar(40) NOT NULL DEFAULT 'gabriel',
  `hambre` tinyint(3) UNSIGNED NOT NULL DEFAULT 80,
  `felicidad` tinyint(3) UNSIGNED NOT NULL DEFAULT 80,
  `temperatura` tinyint(3) UNSIGNED NOT NULL DEFAULT 80,
  `edad` int(11) NOT NULL DEFAULT 0,
  `viva` tinyint(1) NOT NULL DEFAULT 1,
  `eclosionado` tinyint(1) NOT NULL DEFAULT 0,
  `ultima_vez` datetime NOT NULL DEFAULT current_timestamp(),
  `eclosion_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mascota_memoria`
--

CREATE TABLE `mascota_memoria` (
  `user_id` int(11) NOT NULL,
  `clave` varchar(40) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `guardado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mascota_gustos`
--

CREATE TABLE `mascota_gustos` (
  `user_id` int(11) NOT NULL,
  `alimento` varchar(40) NOT NULL,
  `valor` tinyint(3) UNSIGNED NOT NULL DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `text` varchar(2000) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `chat_id`, `from_user_id`, `text`, `sent_at`) VALUES
(1, 1, 1, 'Que tramas morena?', '2026-05-22 10:40:13'),
(2, 1, 2, 'nada moreno', '2026-05-22 10:40:41'),
(3, 1, 1, 'ah bueno', '2026-05-22 10:41:01'),
(4, 1, 2, 'entonces?', '2026-05-22 10:47:56'),
(5, 1, 1, 'a', '2026-05-22 10:48:52'),
(6, 1, 1, 'a', '2026-05-22 10:48:52'),
(7, 1, 1, 'a', '2026-05-22 10:48:53'),
(8, 1, 1, 'a', '2026-05-22 10:48:53'),
(9, 1, 1, 'a', '2026-05-22 10:48:53'),
(10, 1, 1, 'a', '2026-05-22 10:48:53'),
(11, 1, 1, 'a', '2026-05-22 10:48:53'),
(12, 1, 1, 'a', '2026-05-22 10:48:54'),
(13, 1, 1, 'a', '2026-05-22 10:48:54'),
(14, 1, 1, 'a', '2026-05-22 10:48:54'),
(15, 1, 1, 'a', '2026-05-22 10:48:54'),
(16, 1, 1, 'a', '2026-05-22 10:48:54'),
(17, 1, 1, 'a', '2026-05-22 10:48:55'),
(18, 1, 1, 'a', '2026-05-22 10:48:55'),
(19, 1, 1, 'a', '2026-05-22 10:48:55'),
(20, 1, 1, 'a', '2026-05-22 10:48:55'),
(21, 1, 2, 'b', '2026-05-22 10:49:04'),
(22, 1, 1, 'hola', '2026-05-26 16:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `momentos`
--

CREATE TABLE `momentos` (
  `id` int(11) NOT NULL,
  `pareja_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL DEFAULT 1,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `emoji` varchar(10) DEFAULT NULL,
  `emocion` varchar(10) DEFAULT NULL,
  `fecha` date NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `momentos`
--

INSERT INTO `momentos` (`id`, `pareja_id`, `usuario_id`, `titulo`, `descripcion`, `foto`, `emoji`, `emocion`, `fecha`, `creado_en`) VALUES
(7, 1, 2, 'asdadsdad', '', NULL, NULL, '😊', '2026-05-26', '2026-05-26 18:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `music_extras`
--

CREATE TABLE `music_extras` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` varchar(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `artist` varchar(200) DEFAULT '',
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `music_extras`
--

INSERT INTO `music_extras` (`id`, `user_id`, `video_id`, `title`, `artist`, `added_at`) VALUES
(1, 1, 'NVqXG3QNav0', 'The Shattering Circle, or: A Charade of Shadeless Ones and Zeroes Rearranged ad Nihilum', '', '2026-05-26 18:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(40) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `from_user_id`, `payload`, `is_read`, `created_at`) VALUES
(1, 1, 'review', 2, '{\"category\":\"movies\",\"itemTitle\":\"bsdds\",\"mtype\":\"\"}', 1, '2026-05-25 16:37:31'),
(2, 1, 'review', 2, '{\"category\":\"music\",\"itemTitle\":\"Take Care\",\"mtype\":\"song\"}', 1, '2026-05-22 10:17:47'),
(3, 1, 'review', 2, '{\"category\":\"music\",\"itemTitle\":\"Gone Angels\",\"mtype\":\"song\"}', 1, '2026-05-22 10:17:41'),
(4, 1, 'follow', 2, '[]', 1, '2026-05-22 08:26:11'),
(5, 1, 'like', 2, '{\"postId\":2,\"postText\":\"que tal?\"}', 1, '2026-05-22 08:26:10'),
(6, 1, 'like', 2, '{\"postId\":1,\"postText\":\"hola\"}', 1, '2026-05-22 08:26:10'),
(7, 2, 'review', 1, '{\"category\":\"music\",\"itemTitle\":\"Everything is a Lot\",\"mtype\":\"album\"}', 1, '2026-05-25 16:25:28'),
(9, 2, 'review', 1, '{\"category\":\"movies\",\"itemTitle\":\"adada\",\"mtype\":\"\"}', 1, '2026-05-25 16:22:38'),
(10, 2, 'follow', 1, '[]', 1, '2026-05-22 20:38:13'),
(11, 2, 'review', 1, '{\"category\":\"music\",\"itemTitle\":\"No Children\"}', 1, '2026-05-22 10:12:10'),
(12, 2, 'like', 1, '{\"postId\":3}', 1, '2026-05-22 07:57:36'),
(13, 3, 'follow', 1, '[]', 0, '2026-05-25 16:22:15'),
(16, 2, 'review', 1, '{\"category\":\"movies\",\"itemTitle\":\"bbasaw\",\"mtype\":\"\"}', 1, '2026-05-27 12:56:00'),
(20, 2, 'review', 1, '{\"category\":\"music\",\"itemTitle\":\"No Devil Lived On\",\"mtype\":\"song\"}', 1, '2026-05-27 13:26:35'),
(21, 2, 'review', 1, '{\"category\":\"series\",\"itemTitle\":\"zdada\",\"mtype\":\"\"}', 1, '2026-05-27 14:21:43'),
(22, 1, 'like', 2, '{\"postId\":5,\"postText\":\"Chicos creo que me escucho\"}', 1, '2026-05-29 21:10:53'),
(23, 1, 'like', 2, '{\"postId\":6,\"postText\":\"nueva pfp\"}', 1, '2026-05-29 21:10:54'),
(24, 2, 'review', 1, '{\"category\":\"music\",\"itemTitle\":\"Event Horizon (Reach for the Sun and Burn! Burn! Burn!)\",\"mtype\":\"song\"}', 0, '2026-05-29 22:35:17'),
(25, 2, 'review', 1, '{\"category\":\"music\",\"itemTitle\":\"FIRE!!!\",\"mtype\":\"song\"}', 0, '2026-05-29 22:35:28');

-- --------------------------------------------------------

--
-- Table structure for table `ocs`
--

CREATE TABLE `ocs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `creado_por` int(11) DEFAULT 0,
  `nombre` varchar(100) NOT NULL,
  `foto_url` varchar(500) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `edad` varchar(30) DEFAULT NULL,
  `altura` varchar(20) DEFAULT NULL,
  `genero` varchar(30) DEFAULT NULL,
  `ojos` varchar(40) DEFAULT NULL,
  `cabello` varchar(40) DEFAULT NULL,
  `zodiaco` varchar(20) DEFAULT NULL,
  `especie` varchar(50) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `foto_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `alias` varchar(100) DEFAULT NULL,
  `orientacion` varchar(50) DEFAULT NULL,
  `pronombres` varchar(50) DEFAULT NULL,
  `relacion` varchar(100) DEFAULT NULL,
  `etnia` varchar(100) DEFAULT NULL,
  `enneagrama` varchar(20) DEFAULT NULL,
  `mbti` varchar(10) DEFAULT NULL,
  `estatus` varchar(50) DEFAULT NULL,
  `residencia` varchar(100) DEFAULT NULL,
  `alineamiento` varchar(50) DEFAULT NULL,
  `caracter` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` varchar(50) DEFAULT NULL,
  `ocupacion` varchar(100) DEFAULT NULL,
  `peso` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ocs`
--

INSERT INTO `ocs` (`id`, `user_id`, `creado_por`, `nombre`, `foto_url`, `descripcion`, `edad`, `altura`, `genero`, `ojos`, `cabello`, `zodiaco`, `especie`, `orden`, `creado_en`, `actualizado_en`, `foto_id`, `created_at`, `alias`, `orientacion`, `pronombres`, `relacion`, `etnia`, `enneagrama`, `mbti`, `estatus`, `residencia`, `alineamiento`, `caracter`, `fecha_nacimiento`, `ocupacion`, `peso`) VALUES
(5, 2, 0, 'fsdfsd', NULL, '', '', '', '', '', '', '', '', 0, '2026-05-30 15:59:43', '2026-05-30 15:59:43', '', '2026-05-30 15:59:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 2, 0, 'dddd', NULL, '', '', '', '', '', '', '', '', 0, '2026-05-30 15:59:48', '2026-05-30 15:59:48', '', '2026-05-30 15:59:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 2, 0, 'test', NULL, '', '', '', '', '', '', '', '', 0, '2026-05-30 16:02:18', '2026-05-30 16:02:18', '', '2026-05-30 16:02:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 2, 0, 'werewr', NULL, '', '', '', '', '', '', '', '', 0, '2026-05-30 16:02:48', '2026-05-30 16:02:48', '', '2026-05-30 16:02:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 2, 0, 'rere', NULL, 'rere', 'rere', 'rere', 'erre', 'reerre', 'erre', 'rere', 'rere', 0, '2026-05-30 16:16:12', '2026-05-30 16:16:12', '', '2026-05-30 16:16:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `oc_categorias`
--

CREATE TABLE `oc_categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `color` varchar(7) DEFAULT '#808080',
  `user_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `oc_categorias`
--

INSERT INTO `oc_categorias` (`id`, `nombre`, `color`, `user_id`) VALUES
(1, 'Principal', '#5B744B', 0),
(2, 'Secundario', '#799567', 0),
(3, 'Villain', '#c8456e', 0),
(4, 'NPC', '#888888', 0);

-- --------------------------------------------------------

--
-- Table structure for table `oc_categoria_rel`
--

CREATE TABLE `oc_categoria_rel` (
  `oc_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oc_galeria`
--

CREATE TABLE `oc_galeria` (
  `id` int(11) NOT NULL,
  `oc_id` int(11) NOT NULL,
  `foto_url` varchar(500) NOT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `drive_id` varchar(100) NOT NULL DEFAULT '',
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parejas`
--

CREATE TABLE `parejas` (
  `id` int(11) NOT NULL,
  `usuario1_id` int(11) NOT NULL,
  `usuario2_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parejas`
--

INSERT INTO `parejas` (`id`, `usuario1_id`, `usuario2_id`, `fecha_inicio`, `creado_en`) VALUES
(1, 2, 1, '2026-04-11', '2026-05-20 00:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `partner_invites`
--

CREATE TABLE `partner_invites` (
  `id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playlists`
--

CREATE TABLE `playlists` (
  `id` bigint(20) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlists`
--

INSERT INTO `playlists` (`id`, `owner_id`, `name`, `created_at`) VALUES
(1, 1, 'Playlist para bombardear peru', '2026-05-26 18:26:08'),
(2, 1, 'test', '2026-05-26 18:26:08'),
(3, 1, 'test2', '2026-05-26 18:26:08'),
(4, 1, 'test3', '2026-05-26 18:26:08'),
(5, 1, 'test4', '2026-05-26 18:26:08'),
(6, 1, 'test5', '2026-05-26 18:26:08'),
(7, 1, 'dad', '2026-05-26 18:26:08'),
(8, 2, 'Angie serotonine', '2026-05-26 18:26:08'),
(9, 3, 'qeq', '2026-05-26 18:26:08'),
(12, 1, 'sdawdas', '2026-05-26 19:03:23');

-- --------------------------------------------------------

--
-- Table structure for table `playlist_collaborators`
--

CREATE TABLE `playlist_collaborators` (
  `playlist_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlist_collaborators`
--

INSERT INTO `playlist_collaborators` (`playlist_id`, `user_id`, `added_at`) VALUES
(2, 3, '2026-05-26 18:26:08'),
(3, 2, '2026-05-26 18:26:08'),
(12, 2, '2026-05-26 19:21:23');

-- --------------------------------------------------------

--
-- Table structure for table `playlist_invites`
--

CREATE TABLE `playlist_invites` (
  `id` bigint(20) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `playlist_id` bigint(20) NOT NULL,
  `type` varchar(40) NOT NULL DEFAULT 'invite',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playlist_tracks`
--

CREATE TABLE `playlist_tracks` (
  `id` bigint(20) NOT NULL,
  `playlist_id` bigint(20) NOT NULL,
  `position` int(11) NOT NULL,
  `video_id` varchar(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `artist` varchar(200) DEFAULT '',
  `duration` int(11) DEFAULT 0,
  `added_by` varchar(100) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlist_tracks`
--

INSERT INTO `playlist_tracks` (`id`, `playlist_id`, `position`, `video_id`, `title`, `artist`, `duration`, `added_by`) VALUES
(1, 1, 0, 'yZkPe7TEuyk', 'FIRE!!!', 'Vane Lily', 259, 'Capi'),
(2, 1, 1, 'NVqXG3QNav0', 'The Shattering Circle, or: A Charade of Shadeless Ones and Zeroes Rearranged ad Nihilum', 'Heaven Pierce Her', 382, ''),
(3, 1, 2, 'QDYUiCPLtxk', 'In Absentia ΛΟΓΟΣ', 'Heaven Pierce Her', 274, ''),
(4, 1, 3, 'mxKUFhBKmnw', 'Spiral Out (Keep Going)', 'Heaven Pierce Her', 279, ''),
(5, 1, 4, 'XY3b4kVAV2Y', 'Never Odd or Even', 'Heaven Pierce Her', 133, ''),
(6, 1, 5, 'bcOUS1o6bwM', 'Duel (Versus Reprise)', 'Heaven Pierce Her', 365, ''),
(7, 1, 6, '_ysPpT7-f4o', 'No Devil Lived On', 'Heaven Pierce Her', 438, ''),
(8, 1, 7, '9BY8FW1LLqw', 'Mirror Rim', 'Heaven Pierce Her', 184, ''),
(9, 1, 8, 'gtboglNaLgw', 'The Break (Crimson Glass deComposition)', 'Heaven Pierce Her', 263, ''),
(10, 1, 9, 'stpMtEx5zqc', 'Event Horizon (Reach for the Sun and Burn! Burn! Burn!)', 'Heaven Pierce Her', 312, ''),
(11, 1, 10, 'BvPrRkK1I6k', 'The Fall', 'Heaven Pierce Her', 187, ''),
(12, 1, 11, 'Dca9gJyjoAg', 'Salt, Pepper, Birds, and the Thought Police', 'Mili', 244, 'Capi'),
(13, 1, 12, 'Fnb1xYDViDs', 'One Way Or Another - Remastered 2001', 'Blondie', 213, 'Capi'),
(14, 1, 13, 'g4UGCaLg2SY', 'Laplace’s Angel (Hurt People? Hurt People!)', 'Will Wood', 269, 'Capi'),
(15, 1, 14, 'CRQXGpiSz1w', 'Chemical Overreaction / Compound Fracture - 2020 Remastered Version', 'Will Wood and the Tapeworms', 203, 'Capi'),
(16, 1, 15, '0vfZjdK8Ktw', 'The Mind Electric', 'Miracle Musical', 373, 'Capi'),
(17, 1, 16, 'XFPdJM8YQoA', 'Abnormality Dancin\' Girl', 'MICCHI;Drazically', 208, 'Capi'),
(18, 1, 17, 'UnIhRpIT7nc', 'ラグトレイン', '稲葉曇', 252, 'Capi'),
(19, 1, 18, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 145, 'Capi'),
(20, 1, 19, 'fqGKZ3fzN1M', 'No Children', 'The Mountain Goats', 168, 'Capi'),
(21, 1, 20, 'xIoXE4q-Jes', 'Against the Kitchen Floor', 'Will Wood', 306, 'Capi'),
(22, 1, 21, 'rfUeWe7u364', 'Hymn for a Scarecrow', 'Tally Hall', 290, 'Capi'),
(23, 1, 22, 'LmZD-TU96q4', 'IRIS OUT', 'Kenshi Yonezu', 153, 'Capi'),
(24, 1, 23, 'U8BlNEKq0r8', 'Aishite Aishite Aishite', 'Ado', 258, 'Capi'),
(25, 1, 24, 'wqPdeT6Jpdg', 'Wet', 'Dazey and the Scouts', 172, 'Capi'),
(26, 1, 25, 'Lt8AfIeJOxw', 'Paranoid Android', 'Radiohead', 387, 'Capi'),
(27, 1, 26, 'ktpr-HxGJ0E', 'Thank You for the Venom', 'My Chemical Romance', 221, 'Capi'),
(28, 2, 0, 'yZkPe7TEuyk', 'FIRE!!!', 'Vane Lily', 259, 'Capi'),
(29, 2, 1, 'NVqXG3QNav0', 'The Shattering Circle, or: A Charade of Shadeless Ones and Zeroes Rearranged ad Nihilum', 'Heaven Pierce Her', 382, 'Angie'),
(30, 2, 2, 'bcOUS1o6bwM', 'Duel (Versus Reprise)', 'Heaven Pierce Her', 365, 'Capi'),
(31, 2, 3, 'QDYUiCPLtxk', 'In Absentia ΛΟΓΟΣ', 'Heaven Pierce Her', 274, 'Angie'),
(32, 2, 4, 'mxKUFhBKmnw', 'Spiral Out (Keep Going)', 'Heaven Pierce Her', 279, 'Angie'),
(33, 2, 5, 'XY3b4kVAV2Y', 'Never Odd or Even', 'Heaven Pierce Her', 133, 'Angie'),
(34, 2, 6, '_ysPpT7-f4o', 'No Devil Lived On', 'Heaven Pierce Her', 438, 'Angie'),
(35, 2, 7, '9BY8FW1LLqw', 'Mirror Rim', 'Heaven Pierce Her', 184, 'Angie'),
(36, 2, 8, 'gtboglNaLgw', 'The Break (Crimson Glass deComposition)', 'Heaven Pierce Her', 263, 'Angie'),
(37, 2, 9, 'stpMtEx5zqc', 'Event Horizon (Reach for the Sun and Burn! Burn! Burn!)', 'Heaven Pierce Her', 312, 'Angie'),
(38, 3, 0, 'yZkPe7TEuyk', 'FIRE!!!', 'Vane Lily;Jamie Paige', 261, 'Capi'),
(39, 3, 1, 'ktmZQN8V15A', 'Bang Bang Bang', 'BBpanzu', 181, 'Capi'),
(40, 3, 2, 'Dca9gJyjoAg', 'Salt, Pepper, Birds, and the Thought Police', 'Mili', 244, 'Capi'),
(41, 3, 3, 'CgxOQjYBmss', 'Dadadadadaru', 'amala', 159, 'Capi'),
(42, 3, 4, 'AMfG3sMo34s', 'Breaking the Law', 'Judas Priest', 154, 'Capi'),
(43, 3, 5, 'Fnb1xYDViDs', 'One Way Or Another - Remastered 2001', 'Blondie', 213, 'Capi'),
(44, 3, 6, '76ZPTnduToo', 'My Songs Know What You Did In The Dark (Light Em Up)', 'Fall Out Boy', 186, 'Capi'),
(45, 3, 7, 'g4UGCaLg2SY', 'Laplace’s Angel (Hurt People? Hurt People!)', 'Will Wood', 269, 'Capi'),
(46, 3, 8, 'Dao5P8Mqkzw', 'Wrecking Ball', 'Mother Mother', 194, 'Capi'),
(47, 3, 9, 'CRQXGpiSz1w', 'Chemical Overreaction / Compound Fracture - 2020 Remastered Version', 'Will Wood and the Tapeworms', 203, 'Capi'),
(48, 3, 10, '0vfZjdK8Ktw', 'The Mind Electric', 'Miracle Musical', 373, 'Capi'),
(49, 3, 11, 'sHO-cVXWWY0', 'Dramaturgy', 'Eve', 239, 'Capi'),
(50, 3, 12, '_v_Voe5KD1M', 'Re:Re:', 'ASIAN KUNG-FU GENERATION', 229, 'Capi'),
(51, 3, 13, 'XFPdJM8YQoA', 'Abnormality Dancin\' Girl', 'MICCHI;Drazically', 208, 'Capi'),
(52, 3, 14, 'UnIhRpIT7nc', 'ラグトレイン', '稲葉曇', 252, 'Capi'),
(53, 3, 15, '0AK1KVjhUno', 'Spiderhead', 'Cage The Elephant', 223, 'Capi'),
(54, 3, 16, '-KttTf9jyT8', 'Jobless Monday', 'Mitski', 127, 'Capi'),
(55, 3, 17, '_3YYEU4_x_0', '50/50', 'The Strokes', 163, 'Capi'),
(56, 3, 18, 'L9uj2XChyBI', 'Headlock', 'Imogen Heap', 216, 'Capi'),
(57, 3, 19, 'cqwcH_WIP-8', 'It\'s Going Down Now', 'Azumi Takahashi;Lotus Juice;アトラスサウンドチーム;ATLUS GAME MUSIC', 183, 'Capi'),
(58, 3, 20, 'LvYL8u4p-aM', 'Spoken For', 'FLAVOR FOLEY', 254, 'Capi'),
(59, 3, 21, 'mxKUFhBKmnw', 'Spiral Out (Keep Going)', 'Heaven Pierce Her', 279, 'Capi'),
(60, 3, 22, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 145, 'Capi'),
(61, 3, 23, '7LhZOJM7p7k', 'Quiet', 'Lights', 194, 'Capi'),
(62, 3, 24, 'Ie0Ub3-Dx8Y', 'Teen Idle', 'MARINA', 254, 'Capi'),
(63, 3, 25, 'fqGKZ3fzN1M', 'No Children', 'The Mountain Goats', 168, 'Capi'),
(64, 3, 26, '5-I1lT6Jbdo', 'Harpy Hare', 'Yaelokre', 180, 'Capi'),
(65, 3, 27, 'YixAD9GIAuY', '4:00A.M.', 'Taeko Onuki', 335, 'Capi'),
(66, 3, 28, '8Bu3N-2tA_0', 'Impacto', 'Enjambre', 238, 'Capi'),
(67, 3, 29, 'jLXTBbMRxK8', 'Who\'s Ready for Tomorrow', 'RAT BOY;IBDY', 116, 'Capi'),
(68, 3, 30, 'zyhml1UG6ZY', 'FVN!', 'LVL1', 191, 'Capi'),
(69, 3, 31, 'xIoXE4q-Jes', 'Against the Kitchen Floor', 'Will Wood', 306, 'Capi'),
(70, 3, 32, 'cA64x2PQTsU', 'I Deserve to Bleed', 'Sushi Soucy', 104, 'Capi'),
(71, 3, 33, 'rfUeWe7u364', 'Hymn for a Scarecrow', 'Tally Hall', 290, 'Capi'),
(72, 3, 34, '8So3SA2uJvo', 'A Human\'s Touch', 'TWRP;McKenna Rae', 292, 'Capi'),
(73, 3, 35, 'LmZD-TU96q4', 'IRIS OUT', 'Kenshi Yonezu', 153, 'Capi'),
(74, 3, 36, '5NarVgDFNX0', 'アイドル', 'YOASOBI', 226, 'Capi'),
(75, 3, 37, 'U8BlNEKq0r8', 'Aishite Aishite Aishite', 'Ado', 258, 'Capi'),
(76, 3, 38, '19y8YTbvri8', 'メズマライザー (feat. 初音ミク&重音テト)', '32ki;Hatsune Miku;重音テト', 157, 'Capi'),
(77, 3, 39, '9XRIj1_OTxA', 'Old Friend', 'Mitski', 113, 'Capi'),
(78, 3, 40, 'x6yGHOpIe5c', 'Burning Pile', 'Mother Mother', 262, 'Capi'),
(79, 3, 41, 'wqPdeT6Jpdg', 'Wet', 'Dazey and the Scouts', 172, 'Capi'),
(80, 3, 42, 'xjBKcdFU3Hs', 'Guilty Pleasure', 'Chappell Roan', 225, 'Capi'),
(81, 3, 43, 'Lt8AfIeJOxw', 'Paranoid Android', 'Radiohead', 387, 'Capi'),
(82, 3, 44, 'GLg2352T_vc', 'Lovefool', 'The Cardigans', 197, 'Capi'),
(83, 3, 45, 'LNq4xox99HY', 'Ode To The Mets', 'The Strokes', 353, 'Capi'),
(84, 3, 46, 'FhksmAd0O2w', 'Help I\'m Alive', 'Metric', 286, 'Capi'),
(85, 3, 47, 'ktpr-HxGJ0E', 'Thank You for the Venom', 'My Chemical Romance', 221, 'Capi'),
(86, 3, 48, 'OyuyxJPO56c', 'Headfirst Slide Into Cooperstown On A Bad Bet', 'Fall Out Boy', 234, 'Capi'),
(87, 3, 49, 'bcOUS1o6bwM', 'Duel (Versus Reprise)', 'Heaven Pierce Her', 365, 'Angie'),
(88, 8, 0, 'QDYUiCPLtxk', 'In Absentia ΛΟΓΟΣ', 'Heaven Pierce Her', 274, ''),
(89, 8, 1, 'mxKUFhBKmnw', 'Spiral Out (Keep Going)', 'Heaven Pierce Her', 279, ''),
(90, 8, 2, 'XY3b4kVAV2Y', 'Never Odd or Even', 'Heaven Pierce Her', 133, ''),
(91, 8, 3, '_ysPpT7-f4o', 'No Devil Lived On', 'Heaven Pierce Her', 438, ''),
(92, 8, 4, '9BY8FW1LLqw', 'Mirror Rim', 'Heaven Pierce Her', 184, ''),
(93, 8, 5, 'gtboglNaLgw', 'The Break (Crimson Glass deComposition)', 'Heaven Pierce Her', 263, ''),
(94, 8, 6, 'NVqXG3QNav0', 'The Shattering Circle, or: A Charade of Shadeless Ones and Zeroes Rearranged ad Nihilum', 'Heaven Pierce Her', 382, ''),
(95, 8, 7, 'stpMtEx5zqc', 'Event Horizon (Reach for the Sun and Burn! Burn! Burn!)', 'Heaven Pierce Her', 312, ''),
(96, 8, 8, 'BvPrRkK1I6k', 'The Fall', 'Heaven Pierce Her', 187, ''),
(97, 9, 0, 'bcOUS1o6bwM', 'Duel (Versus Reprise)', 'Heaven Pierce Her', 365, 'Unroudmell'),
(99, 12, 0, 'QDYUiCPLtxk', 'In Absentia ΛΟΓΟΣ', 'Heaven Pierce Her', 274, 'Capi'),
(100, 12, 1, '_Uv6KNla9OQ', 'Spiral Out (Keep Going) - Time Signature Visualizer | ULTRAKILL 8-1 OST', 'RedstoneWizard08', 273, 'Capi'),
(101, 12, 2, 'XY3b4kVAV2Y', 'Never Odd or Even', 'Heaven Pierce Her', 133, 'Capi'),
(102, 12, 3, '_ysPpT7-f4o', 'No Devil Lived On', 'Heaven Pierce Her', 438, 'Capi'),
(103, 12, 4, '9BY8FW1LLqw', 'Mirror Rim', 'Heaven Pierce Her', 184, 'Capi'),
(104, 12, 5, 'gtboglNaLgw', 'The Break (Crimson Glass deComposition)', 'Heaven Pierce Her', 263, 'Capi'),
(105, 12, 6, 'NVqXG3QNav0', 'The Shattering Circle, or: A Charade of Shadeless Ones and Zeroes Rearranged ad Nihilum', 'Heaven Pierce Her', 382, 'Capi'),
(106, 12, 7, 'stpMtEx5zqc', 'Event Horizon (Reach for the Sun and Burn! Burn! Burn!)', 'Heaven Pierce Her', 312, 'Capi'),
(107, 12, 8, 'BvPrRkK1I6k', 'The Fall', 'Heaven Pierce Her', 187, 'Capi');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `text` varchar(1000) NOT NULL,
  `image_url` varchar(2000) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `text`, `image_url`, `created_at`) VALUES
(1, 1, 'hola', NULL, '2026-05-20 11:04:58'),
(2, 1, 'que tal?', NULL, '2026-05-20 11:05:03'),
(3, 2, 'que tramais morenos?', NULL, '2026-05-22 07:11:30'),
(9, 1, 'Chicos creo que me escucho', 'https://drive.google.com/thumbnail?id=1fnCOVPD-6jRvo_GC20e3cMqzMzxFhj9p&sz=w1920', '2026-05-29 21:22:41'),
(10, 1, 'nueva pfp', 'https://drive.google.com/thumbnail?id=1T3S9mLuIKMaAn2Uiubx4IQQS7E5UlnBW&sz=w1920', '2026-05-29 21:25:32');

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `id` bigint(20) NOT NULL,
  `post_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `text` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_comments`
--

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `text`, `created_at`) VALUES
(1, 3, 1, 'nada morena', '2026-05-29 21:19:21');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `post_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `liked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`post_id`, `user_id`, `liked_at`) VALUES
(1, 2, '2026-05-26 18:26:08'),
(2, 2, '2026-05-26 18:26:08'),
(3, 1, '2026-05-26 18:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `user_id` int(11) NOT NULL,
  `quote` varchar(500) DEFAULT '',
  `bio` text DEFAULT NULL,
  `pronouns` varchar(30) DEFAULT NULL,
  `age` tinyint(3) UNSIGNED DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `steam` varchar(200) DEFAULT NULL,
  `discord` varchar(100) DEFAULT NULL,
  `twitter` varchar(100) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`user_id`, `quote`, `bio`, `pronouns`, `age`, `country`, `steam`, `discord`, `twitter`, `instagram`, `updated_at`) VALUES
(1, '', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut a', 'I/Me/Myself', 19, 'España', 'ada', 'CapiWasTaken_', 'CapiWasTaken', 'capiwastaken', '2026-05-26 18:26:08'),
(2, '', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut a', 'They/them', 25, 'España', 'a', 'a', 'a', 'a', '2026-05-26 18:26:08'),
(3, '', 'asdadawdasdwadasdasd', '', NULL, '', '', '', '', '', '2026-05-26 18:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `recordatorios`
--

CREATE TABLE `recordatorios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `pareja_id` int(11) NOT NULL DEFAULT 0,
  `titulo` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('cita','examen','aniversario','otro') DEFAULT 'otro',
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recordatorios`
--

INSERT INTO `recordatorios` (`id`, `usuario_id`, `pareja_id`, `titulo`, `fecha`, `tipo`, `descripcion`, `creado_en`) VALUES
(3, 2, 1, 'awdasdawd', '2026-05-26', 'cita', '', '2026-05-26 18:42:49');

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`colors`)),
  `wallpaper` varchar(255) DEFAULT NULL,
  `start_icon` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `is_downloaded` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `user_id`, `name`, `colors`, `wallpaper`, `start_icon`, `is_active`, `is_public`, `is_downloaded`, `updated_at`) VALUES
(2, 2, 'Angie', '{\"winBg\":\"#F8D0C8\",\"winBodyBg\":\"#F8D0C8\",\"surfaceDeep\":\"#F8D0C8\",\"insetBg\":\"#F3BABA\",\"inputBg\":\"#F3BABA\",\"inputText\":\"#1a1a1a\",\"btnBg\":\"#5B744B\",\"btnText\":\"#F9DDD8\",\"text\":\"#35522B\",\"textMuted\":\"#5B744B\",\"textFaint\":\"#799567\",\"textInset\":\"#799567\",\"titlebarStart\":\"#5B744B\",\"titlebarEnd\":\"#799567\",\"titlebarText\":\"#F9DDD8\",\"titlebarIconColor\":\"#35522B\",\"titlebarIconBg\":\"#F8D0C8\",\"titlebarIconBezelLight\":\"#F9DDD8\",\"titlebarIconBezelDark\":\"#35522B\",\"accent\":\"#799567\",\"accentText\":\"#F9DDD8\",\"accentDeep\":\"#5B744B\",\"border\":\"#F3BABA\",\"borderStrong\":\"#5B744B\",\"bezelLight1\":\"#F9DDD8\",\"bezelLight2\":\"#F8D0C8\",\"bezelDark1\":\"#35522B\",\"bezelDark2\":\"#5B744B\",\"desktopBg\":\"#F9DDD8\",\"linkText\":\"#5B744B\",\"errorText\":\"#c8456e\",\"warningBg\":\"#F3BABA\",\"warningText\":\"#35522B\",\"selectionBg\":\"#799567\",\"selectionText\":\"#F9DDD8\",\"badgeBg\":\"#c8456e\",\"badgeText\":\"#F9DDD8\",\"starColor\":\"#c8a000\"}', NULL, NULL, 0, 1, 0, '2026-05-28 18:37:19'),
(14, 3, 'Forthebrainrot', '{\"winBg\":\"#f9f06b\",\"winBodyBg\":\"#241f31\",\"surfaceDeep\":\"#26203c\",\"insetBg\":\"#808080\",\"inputBg\":\"#241f31\",\"inputText\":\"#ffffff\",\"btnBg\":\"#cb3a3a\",\"btnText\":\"#ffffff\",\"startBtnBg\":\"#1a5fb4\",\"startBtnText\":\"#000000\",\"text\":\"#2ec27e\",\"textMuted\":\"#666666\",\"textFaint\":\"#808080\",\"textInset\":\"#808080\",\"titlebarStart\":\"#57e389\",\"titlebarEnd\":\"#26a269\",\"titlebarText\":\"#e60000\",\"accent\":\"#813d9c\",\"accentText\":\"#792a2a\",\"accentDeep\":\"#00004a\",\"titlebarIconColor\":\"#000000\",\"titlebarIconBg\":\"#c0c0c0\",\"titlebarIconBezelLight\":\"#ffffff\",\"titlebarIconBezelDark\":\"#0a0a0a\",\"border\":\"#808080\",\"borderStrong\":\"#404040\",\"bezelLight1\":\"#000000\",\"bezelLight2\":\"#613583\",\"bezelDark1\":\"#9141ac\",\"bezelDark2\":\"#000000\",\"desktopBg\":\"#008080\",\"linkText\":\"#f5c211\",\"errorText\":\"#c00000\",\"warningBg\":\"#fffbe6\",\"warningText\":\"#444444\",\"selectionBg\":\"#000080\",\"selectionText\":\"#f9f06b\",\"badgeBg\":\"#d72638\",\"badgeText\":\"#f9f06b\",\"starColor\":\"#ffd700\"}', 'assets/img/wallpapers/theme-Forthebrainrot-unroudmell-wallpaper.jpg', NULL, 1, 1, 0, '2026-05-29 16:09:51'),
(29, 1, 'Angie', '{\"winBg\":\"#F8D0C8\",\"winBodyBg\":\"#F8D0C8\",\"surfaceDeep\":\"#F8D0C8\",\"insetBg\":\"#F3BABA\",\"inputBg\":\"#F3BABA\",\"inputText\":\"#1a1a1a\",\"btnBg\":\"#5B744B\",\"btnText\":\"#F9DDD8\",\"text\":\"#35522B\",\"textMuted\":\"#5B744B\",\"textFaint\":\"#799567\",\"textInset\":\"#799567\",\"titlebarStart\":\"#5B744B\",\"titlebarEnd\":\"#799567\",\"titlebarText\":\"#F9DDD8\",\"titlebarIconColor\":\"#35522B\",\"titlebarIconBg\":\"#F8D0C8\",\"titlebarIconBezelLight\":\"#F9DDD8\",\"titlebarIconBezelDark\":\"#35522B\",\"accent\":\"#799567\",\"accentText\":\"#F9DDD8\",\"accentDeep\":\"#5B744B\",\"border\":\"#F3BABA\",\"borderStrong\":\"#5B744B\",\"bezelLight1\":\"#F9DDD8\",\"bezelLight2\":\"#F8D0C8\",\"bezelDark1\":\"#35522B\",\"bezelDark2\":\"#5B744B\",\"desktopBg\":\"#F9DDD8\",\"linkText\":\"#5B744B\",\"errorText\":\"#c8456e\",\"warningBg\":\"#F3BABA\",\"warningText\":\"#35522B\",\"selectionBg\":\"#799567\",\"selectionText\":\"#F9DDD8\",\"badgeBg\":\"#c8456e\",\"badgeText\":\"#F9DDD8\",\"starColor\":\"#c8a000\"}', 'assets/img/wallpapers/theme-Angie-capi-wallpaper.png', NULL, 0, 0, 1, '2026-05-27 19:21:25'),
(38, 3, 'Angie', '{\"winBg\":\"#F8D0C8\",\"winBodyBg\":\"#F8D0C8\",\"surfaceDeep\":\"#F8D0C8\",\"insetBg\":\"#F3BABA\",\"inputBg\":\"#F3BABA\",\"inputText\":\"#1a1a1a\",\"btnBg\":\"#5B744B\",\"btnText\":\"#F9DDD8\",\"text\":\"#35522B\",\"textMuted\":\"#5B744B\",\"textFaint\":\"#799567\",\"textInset\":\"#799567\",\"titlebarStart\":\"#5B744B\",\"titlebarEnd\":\"#799567\",\"titlebarText\":\"#F9DDD8\",\"titlebarIconColor\":\"#35522B\",\"titlebarIconBg\":\"#F8D0C8\",\"titlebarIconBezelLight\":\"#F9DDD8\",\"titlebarIconBezelDark\":\"#35522B\",\"accent\":\"#799567\",\"accentText\":\"#F9DDD8\",\"accentDeep\":\"#5B744B\",\"border\":\"#F3BABA\",\"borderStrong\":\"#5B744B\",\"bezelLight1\":\"#F9DDD8\",\"bezelLight2\":\"#F8D0C8\",\"bezelDark1\":\"#35522B\",\"bezelDark2\":\"#5B744B\",\"desktopBg\":\"#F9DDD8\",\"linkText\":\"#5B744B\",\"errorText\":\"#c8456e\",\"warningBg\":\"#F3BABA\",\"warningText\":\"#35522B\",\"selectionBg\":\"#799567\",\"selectionText\":\"#F9DDD8\",\"badgeBg\":\"#c8456e\",\"badgeText\":\"#F9DDD8\",\"starColor\":\"#c8a000\"}', 'assets/img/wallpapers/theme-Angie-unroudmell-wallpaper.png', NULL, 0, 0, 1, '2026-05-27 19:31:10'),
(39, 1, 'Capi', '{\"winBg\":\"#2d2d2d\",\"winBodyBg\":\"#222222\",\"surfaceDeep\":\"#1e1e1e\",\"insetBg\":\"#111111\",\"inputBg\":\"#1a1a1a\",\"inputText\":\"#d0d0d0\",\"btnBg\":\"#3a3a3a\",\"btnText\":\"#d0d0d0\",\"startBtnBg\":\"#c8a000\",\"startBtnText\":\"#000000\",\"text\":\"#d0d0d0\",\"textMuted\":\"#888888\",\"textFaint\":\"#666666\",\"textInset\":\"#444444\",\"titlebarStart\":\"#6b5500\",\"titlebarEnd\":\"#EDC001\",\"titlebarText\":\"#ffffff\",\"accent\":\"#bc9800\",\"accentText\":\"#000000\",\"accentDeep\":\"#c8a000\",\"titlebarIconColor\":\"#000000\",\"titlebarIconBg\":\"#c0c0c0\",\"titlebarIconBezelLight\":\"#ffffff\",\"titlebarIconBezelDark\":\"#0a0a0a\",\"border\":\"#3a3a3a\",\"borderStrong\":\"#555555\",\"bezelLight1\":\"#555555\",\"bezelLight2\":\"#444444\",\"bezelDark1\":\"#111111\",\"bezelDark2\":\"#1a1a1a\",\"desktopBg\":\"#1a1a1a\",\"linkText\":\"#EDC001\",\"errorText\":\"#ff4f6e\",\"warningBg\":\"#2a2200\",\"warningText\":\"#e8cc80\",\"selectionBg\":\"#EDC001\",\"selectionText\":\"#000000\",\"badgeBg\":\"#ff4f6e\",\"badgeText\":\"#ffffff\",\"starColor\":\"#EDC001\"}', 'assets/img/wallpapers/theme-Capi-capi-wallpaper.jpg', 'assets/img/start-icons/theme-Capi-capi-start-icon.png', 1, 1, 0, '2026-05-27 20:14:57'),
(40, 2, 'Angie_2', '{\"winBg\":\"#F8D0C8\",\"winBodyBg\":\"#F8D0C8\",\"surfaceDeep\":\"#F8D0C8\",\"insetBg\":\"#F3BABA\",\"inputBg\":\"#F3BABA\",\"inputText\":\"#1a1a1a\",\"btnBg\":\"#5B744B\",\"btnText\":\"#F9DDD8\",\"text\":\"#35522B\",\"textMuted\":\"#5B744B\",\"textFaint\":\"#799567\",\"textInset\":\"#799567\",\"titlebarStart\":\"#5B744B\",\"titlebarEnd\":\"#799567\",\"titlebarText\":\"#F9DDD8\",\"titlebarIconColor\":\"#35522B\",\"titlebarIconBg\":\"#F8D0C8\",\"titlebarIconBezelLight\":\"#F9DDD8\",\"titlebarIconBezelDark\":\"#35522B\",\"accent\":\"#799567\",\"accentText\":\"#F9DDD8\",\"accentDeep\":\"#5B744B\",\"border\":\"#F3BABA\",\"borderStrong\":\"#5B744B\",\"bezelLight1\":\"#F9DDD8\",\"bezelLight2\":\"#F8D0C8\",\"bezelDark1\":\"#35522B\",\"bezelDark2\":\"#5B744B\",\"desktopBg\":\"#F9DDD8\",\"linkText\":\"#5B744B\",\"errorText\":\"#c8456e\",\"warningBg\":\"#F3BABA\",\"warningText\":\"#35522B\",\"selectionBg\":\"#799567\",\"selectionText\":\"#F9DDD8\",\"badgeBg\":\"#c8456e\",\"badgeText\":\"#F9DDD8\",\"starColor\":\"#c8a000\"}', 'assets/img/wallpapers/theme-Angie_2-angie-wallpaper.png', NULL, 1, 0, 1, '2026-05-28 18:37:19'),
(41, 7, 'Unicorn', '{\"winBg\":\"#c0c0c0\",\"winBodyBg\":\"#c0c0c0\",\"surfaceDeep\":\"#c0c0c0\",\"insetBg\":\"#808080\",\"inputBg\":\"#ffffff\",\"inputText\":\"#000000\",\"btnBg\":\"#c0c0c0\",\"btnText\":\"#000000\",\"startBtnBg\":\"#c0c0c0\",\"startBtnText\":\"#000000\",\"text\":\"#000000\",\"textMuted\":\"#666666\",\"textFaint\":\"#808080\",\"textInset\":\"#808080\",\"titlebarStart\":\"#ed333b\",\"titlebarEnd\":\"#77767b\",\"titlebarText\":\"#ffffff\",\"accent\":\"#000080\",\"accentText\":\"#ffffff\",\"accentDeep\":\"#00004a\",\"titlebarIconColor\":\"#000000\",\"titlebarIconBg\":\"#c0c0c0\",\"titlebarIconBezelLight\":\"#ffffff\",\"titlebarIconBezelDark\":\"#0a0a0a\",\"border\":\"#808080\",\"borderStrong\":\"#404040\",\"bezelLight1\":\"#ffffff\",\"bezelLight2\":\"#dfdfdf\",\"bezelDark1\":\"#0a0a0a\",\"bezelDark2\":\"#808080\",\"desktopBg\":\"#008080\",\"linkText\":\"#0000ff\",\"errorText\":\"#c00000\",\"warningBg\":\"#fffbe6\",\"warningText\":\"#444444\",\"selectionBg\":\"#000080\",\"selectionText\":\"#ffffff\",\"badgeBg\":\"#d72638\",\"badgeText\":\"#ffffff\",\"starColor\":\"#ffd700\"}', 'assets/img/wallpapers/theme-Unicorn-sam-wallpaper.jpeg', NULL, 1, 0, 0, '2026-05-29 16:10:27'),
(44, 7, 'Forthebrainrot', '{\"winBg\":\"#f9f06b\",\"winBodyBg\":\"#241f31\",\"surfaceDeep\":\"#26203c\",\"insetBg\":\"#808080\",\"inputBg\":\"#241f31\",\"inputText\":\"#ffffff\",\"btnBg\":\"#cb3a3a\",\"btnText\":\"#ffffff\",\"startBtnBg\":\"#1a5fb4\",\"startBtnText\":\"#000000\",\"text\":\"#2ec27e\",\"textMuted\":\"#666666\",\"textFaint\":\"#808080\",\"textInset\":\"#808080\",\"titlebarStart\":\"#57e389\",\"titlebarEnd\":\"#26a269\",\"titlebarText\":\"#e60000\",\"accent\":\"#813d9c\",\"accentText\":\"#792a2a\",\"accentDeep\":\"#00004a\",\"titlebarIconColor\":\"#000000\",\"titlebarIconBg\":\"#c0c0c0\",\"titlebarIconBezelLight\":\"#ffffff\",\"titlebarIconBezelDark\":\"#0a0a0a\",\"border\":\"#808080\",\"borderStrong\":\"#404040\",\"bezelLight1\":\"#000000\",\"bezelLight2\":\"#613583\",\"bezelDark1\":\"#9141ac\",\"bezelDark2\":\"#000000\",\"desktopBg\":\"#008080\",\"linkText\":\"#f5c211\",\"errorText\":\"#c00000\",\"warningBg\":\"#fffbe6\",\"warningText\":\"#444444\",\"selectionBg\":\"#000080\",\"selectionText\":\"#f9f06b\",\"badgeBg\":\"#d72638\",\"badgeText\":\"#f9f06b\",\"starColor\":\"#ffd700\"}', 'assets/img/wallpapers/theme-Forthebrainrot-sam-wallpaper.jpg', 'assets/img/start-icons/theme-Forthebrainrot-sam-start-icon.png', 0, 0, 1, '2026-05-29 16:10:27'),
(45, 8, 'Forthebrainrot', '{\"winBg\":\"#f9f06b\",\"winBodyBg\":\"#241f31\",\"surfaceDeep\":\"#26203c\",\"insetBg\":\"#808080\",\"inputBg\":\"#241f31\",\"inputText\":\"#ffffff\",\"btnBg\":\"#cb3a3a\",\"btnText\":\"#ffffff\",\"startBtnBg\":\"#1a5fb4\",\"startBtnText\":\"#000000\",\"text\":\"#2ec27e\",\"textMuted\":\"#666666\",\"textFaint\":\"#808080\",\"textInset\":\"#808080\",\"titlebarStart\":\"#57e389\",\"titlebarEnd\":\"#26a269\",\"titlebarText\":\"#e60000\",\"accent\":\"#813d9c\",\"accentText\":\"#792a2a\",\"accentDeep\":\"#00004a\",\"titlebarIconColor\":\"#000000\",\"titlebarIconBg\":\"#c0c0c0\",\"titlebarIconBezelLight\":\"#ffffff\",\"titlebarIconBezelDark\":\"#0a0a0a\",\"border\":\"#808080\",\"borderStrong\":\"#404040\",\"bezelLight1\":\"#000000\",\"bezelLight2\":\"#613583\",\"bezelDark1\":\"#9141ac\",\"bezelDark2\":\"#000000\",\"desktopBg\":\"#008080\",\"linkText\":\"#f5c211\",\"errorText\":\"#c00000\",\"warningBg\":\"#fffbe6\",\"warningText\":\"#444444\",\"selectionBg\":\"#000080\",\"selectionText\":\"#f9f06b\",\"badgeBg\":\"#d72638\",\"badgeText\":\"#f9f06b\",\"starColor\":\"#ffd700\"}', 'assets/img/wallpapers/theme-Forthebrainrot-syliconna-wallpaper.webp', 'assets/img/start-icons/theme-Forthebrainrot-syliconna-start-icon.png', 1, 0, 1, '2026-05-29 16:18:08');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `user_id` int(11) NOT NULL,
  `key_name` varchar(60) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`value`)),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`user_id`, `key_name`, `value`, `updated_at`) VALUES
(1, 'player', '{\"playlistId\":2,\"trackIndex\":0,\"volume\":18}', '2026-05-29 22:35:23'),
(2, 'player', '{\"playlistId\":12,\"trackIndex\":7}', '2026-05-29 19:37:22'),
(8, 'player', '{\"playlistId\":null,\"trackIndex\":0}', '2026-05-29 16:21:27');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `user_key` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  `discord_webhook` varchar(500) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `user_key`, `username`, `label`, `discord_webhook`, `password`, `avatar`, `creado_en`) VALUES
(1, 'user1', 'capi', 'Capi', 'https://discord.com/api/webhooks/1510034845689053215/uBn0zpxfqG73UOkDIqS9E4dJjZbr1OqNtlNzVUO36ztjT_31gcajrulSx3AfjN4r5ftI', '$2y$10$n78/ytMsutBqvTge48Wijuzaau.q.s1yq15ionEkC7Q5H6eHgTFla', 'Capi', '2026-05-19 23:32:15'),
(2, 'user2', 'angie', 'Angie', 'https://discord.com/api/webhooks/1510034845689053215/uBn0zpxfqG73UOkDIqS9E4dJjZbr1OqNtlNzVUO36ztjT_31gcajrulSx3AfjN4r5ftI', '$2y$10$Y7xcgTMcu2i1BQB/8KbFCOYxPVtzR5vAYCabsVQ7/6.TnhERgFLca', 'Angie', '2026-05-19 23:32:15'),
(3, 'user3', 'unroudmell', 'Unroudmell', 'https://discord.com/api/webhooks/1510034845689053215/uBn0zpxfqG73UOkDIqS9E4dJjZbr1OqNtlNzVUO36ztjT_31gcajrulSx3AfjN4r5ftI', '$2y$10$Zc0b2lwvF0.DEuqXx5M.oe08vO3EbsLnMqsxMEG4L3zkBAfZ5Hqei', NULL, '2026-05-26 18:22:55'),
(7, 'user4', 'sam', 'Sam', 'https://discord.com/api/webhooks/1510034845689053215/uBn0zpxfqG73UOkDIqS9E4dJjZbr1OqNtlNzVUO36ztjT_31gcajrulSx3AfjN4r5ftI', '$2y$10$JkgvE116oXnfq1ywksUOQ.RbMCAeravWbFgU9qgoEUNRAXnfoszYO', NULL, '2026-05-29 16:01:55'),
(8, 'user5', 'syliconna', 'Syliconna', 'https://discord.com/api/webhooks/1510034845689053215/uBn0zpxfqG73UOkDIqS9E4dJjZbr1OqNtlNzVUO36ztjT_31gcajrulSx3AfjN4r5ftI', '$2y$10$XMtb7M58VFMNSQ6T2yZNveAjMq3SyDPqv3erIicIxHei.qhgmbc0e', NULL, '2026-05-29 16:05:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_cache`
--
ALTER TABLE `app_cache`
  ADD PRIMARY KEY (`cache_key`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_chats_pair` (`user_a`,`user_b`),
  ADD KEY `user_b` (`user_b`);

--
-- Indexes for table `desktop_folders`
--
ALTER TABLE `desktop_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dfolders_user` (`user_id`);

--
-- Indexes for table `desktop_folder_items`
--
ALTER TABLE `desktop_folder_items`
  ADD PRIMARY KEY (`folder_id`,`icon_id`);

--
-- Indexes for table `desktop_icons`
--
ALTER TABLE `desktop_icons`
  ADD PRIMARY KEY (`user_id`,`icon_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`follower_id`,`followee_id`),
  ADD KEY `idx_follows_followee` (`followee_id`);

--
-- Indexes for table `item_invites`
--
ALTER TABLE `item_invites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_iinv_to_created` (`to_user_id`,`created_at`),
  ADD KEY `from_user_id` (`from_user_id`),
  ADD KEY `idx_iinv_type` (`type`);

--
-- Indexes for table `list_items`
--
ALTER TABLE `list_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_items_owner_cat` (`owner_id`,`category`),
  ADD KEY `idx_items_shared` (`shared_from`),
  ADD KEY `idx_items_reviewed` (`reviewed_at`);

--
-- Indexes for table `list_item_collaborators`
--
ALTER TABLE `list_item_collaborators`
  ADD PRIMARY KEY (`item_id`,`user_id`),
  ADD KEY `idx_collab_user` (`user_id`);

--
-- Indexes for table `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `mascota_memoria`
--
ALTER TABLE `mascota_memoria`
  ADD PRIMARY KEY (`user_id`,`clave`);

--
-- Indexes for table `mascota_gustos`
--
ALTER TABLE `mascota_gustos`
  ADD PRIMARY KEY (`user_id`,`alimento`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_msg_chat_sent` (`chat_id`,`sent_at`),
  ADD KEY `from_user_id` (`from_user_id`);

--
-- Indexes for table `momentos`
--
ALTER TABLE `momentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pareja_id` (`pareja_id`);

--
-- Indexes for table `music_extras`
--
ALTER TABLE `music_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mex_user` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifs_user_read` (`user_id`,`is_read`,`created_at`),
  ADD KEY `from_user_id` (`from_user_id`);

--
-- Indexes for table `ocs`
--
ALTER TABLE `ocs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ocs_creado_por` (`creado_por`);

--
-- Indexes for table `oc_categorias`
--
ALTER TABLE `oc_categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_oc_categoria_nombre` (`nombre`);

--
-- Indexes for table `oc_categoria_rel`
--
ALTER TABLE `oc_categoria_rel`
  ADD PRIMARY KEY (`oc_id`,`categoria_id`),
  ADD KEY `oc_catrel_cat` (`categoria_id`);

--
-- Indexes for table `oc_galeria`
--
ALTER TABLE `oc_galeria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ocgal_oc` (`oc_id`);

--
-- Indexes for table `parejas`
--
ALTER TABLE `parejas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario1_id` (`usuario1_id`),
  ADD KEY `usuario2_id` (`usuario2_id`);

--
-- Indexes for table `partner_invites`
--
ALTER TABLE `partner_invites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pinv_pair` (`to_user_id`,`from_user_id`),
  ADD KEY `from_user_id` (`from_user_id`);

--
-- Indexes for table `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pl_owner` (`owner_id`);

--
-- Indexes for table `playlist_collaborators`
--
ALTER TABLE `playlist_collaborators`
  ADD PRIMARY KEY (`playlist_id`,`user_id`),
  ADD KEY `idx_plcol_user` (`user_id`);

--
-- Indexes for table `playlist_invites`
--
ALTER TABLE `playlist_invites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_plinv_to_created` (`to_user_id`,`created_at`),
  ADD KEY `from_user_id` (`from_user_id`),
  ADD KEY `playlist_id` (`playlist_id`);

--
-- Indexes for table `playlist_tracks`
--
ALTER TABLE `playlist_tracks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tr_playlist_pos` (`playlist_id`,`position`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posts_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post` (`post_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`post_id`,`user_id`),
  ADD KEY `idx_post_likes_user` (`user_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `recordatorios`
--
ALTER TABLE `recordatorios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_themes_user_name` (`user_id`,`name`),
  ADD KEY `idx_themes_active` (`user_id`,`is_active`),
  ADD KEY `idx_themes_public` (`is_public`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`user_id`,`key_name`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `user_key` (`user_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `item_invites`
--
ALTER TABLE `item_invites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `list_items`
--
ALTER TABLE `list_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `momentos`
--
ALTER TABLE `momentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `music_extras`
--
ALTER TABLE `music_extras`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `ocs`
--
ALTER TABLE `ocs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `oc_categorias`
--
ALTER TABLE `oc_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `oc_galeria`
--
ALTER TABLE `oc_galeria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parejas`
--
ALTER TABLE `parejas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `partner_invites`
--
ALTER TABLE `partner_invites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `playlists`
--
ALTER TABLE `playlists`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `playlist_invites`
--
ALTER TABLE `playlist_invites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `playlist_tracks`
--
ALTER TABLE `playlist_tracks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `recordatorios`
--
ALTER TABLE `recordatorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`user_a`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`user_b`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `desktop_folders`
--
ALTER TABLE `desktop_folders`
  ADD CONSTRAINT `desktop_folders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `desktop_folder_items`
--
ALTER TABLE `desktop_folder_items`
  ADD CONSTRAINT `desktop_folder_items_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `desktop_folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `desktop_icons`
--
ALTER TABLE `desktop_icons`
  ADD CONSTRAINT `desktop_icons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`followee_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_invites`
--
ALTER TABLE `item_invites`
  ADD CONSTRAINT `item_invites_ibfk_1` FOREIGN KEY (`to_user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_invites_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `list_items`
--
ALTER TABLE `list_items`
  ADD CONSTRAINT `list_items_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `list_items_ibfk_2` FOREIGN KEY (`shared_from`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `list_item_collaborators`
--
ALTER TABLE `list_item_collaborators`
  ADD CONSTRAINT `list_item_collaborators_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `list_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `list_item_collaborators_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `momentos`
--
ALTER TABLE `momentos`
  ADD CONSTRAINT `momentos_ibfk_1` FOREIGN KEY (`pareja_id`) REFERENCES `parejas` (`id`);

--
-- Constraints for table `music_extras`
--
ALTER TABLE `music_extras`
  ADD CONSTRAINT `music_extras_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `oc_categoria_rel`
--
ALTER TABLE `oc_categoria_rel`
  ADD CONSTRAINT `oc_catrel_cat` FOREIGN KEY (`categoria_id`) REFERENCES `oc_categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `oc_catrel_oc` FOREIGN KEY (`oc_id`) REFERENCES `ocs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `oc_galeria`
--
ALTER TABLE `oc_galeria`
  ADD CONSTRAINT `oc_galeria_ibfk_1` FOREIGN KEY (`oc_id`) REFERENCES `ocs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parejas`
--
ALTER TABLE `parejas`
  ADD CONSTRAINT `parejas_ibfk_1` FOREIGN KEY (`usuario1_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `parejas_ibfk_2` FOREIGN KEY (`usuario2_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `partner_invites`
--
ALTER TABLE `partner_invites`
  ADD CONSTRAINT `partner_invites_ibfk_1` FOREIGN KEY (`to_user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partner_invites_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `playlists`
--
ALTER TABLE `playlists`
  ADD CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `playlist_collaborators`
--
ALTER TABLE `playlist_collaborators`
  ADD CONSTRAINT `playlist_collaborators_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playlist_collaborators_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `playlist_invites`
--
ALTER TABLE `playlist_invites`
  ADD CONSTRAINT `playlist_invites_ibfk_1` FOREIGN KEY (`to_user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playlist_invites_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playlist_invites_ibfk_3` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `playlist_tracks`
--
ALTER TABLE `playlist_tracks`
  ADD CONSTRAINT `playlist_tracks_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `fk_pc_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pc_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recordatorios`
--
ALTER TABLE `recordatorios`
  ADD CONSTRAINT `recordatorios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `themes`
--
ALTER TABLE `themes`
  ADD CONSTRAINT `themes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
