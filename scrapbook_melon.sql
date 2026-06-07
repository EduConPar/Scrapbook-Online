-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2026 at 06:28 PM
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
(2, 'archive-icon', 0, 0, '2026-06-06 12:22:53'),
(2, 'calendar-icon', 96, 0, '2026-06-04 22:50:17'),
(2, 'companion-icon', 0, 288, '2026-05-30 16:32:44'),
(2, 'dibujo-icon', 96, 288, '2026-05-30 16:32:50'),
(2, 'dnd-icon', 96, 96, '2026-06-06 12:23:01'),
(2, 'galeria-icon', 96, 192, '2026-05-30 16:32:48'),
(2, 'mascota-icon', 192, 0, '2026-06-06 12:22:56'),
(2, 'profile-icon', 0, 96, '2026-05-28 18:37:39'),
(2, 'temas-icon', 0, 192, '2026-06-06 12:22:58'),
(2, 'tienda-icon', 0, 384, '2026-06-04 21:52:21');

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
-- Table structure for table `listening_invites`
--

CREATE TABLE `listening_invites` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listening_participants`
--

CREATE TABLE `listening_participants` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `left_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listening_sessions`
--

CREATE TABLE `listening_sessions` (
  `id` int(11) NOT NULL,
  `host_user_id` int(11) NOT NULL,
  `video_id` varchar(20) DEFAULT NULL,
  `track_title` varchar(255) DEFAULT NULL,
  `track_artist` varchar(255) DEFAULT NULL,
  `cover_url` varchar(500) DEFAULT NULL,
  `current_time_s` int(11) NOT NULL DEFAULT 0,
  `duration_s` int(11) NOT NULL DEFAULT 0,
  `is_playing` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL
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
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL DEFAULT 'Meloncio',
  `skin` enum('meloncio','helldiver','v1') NOT NULL DEFAULT 'meloncio',
  `hambre` tinyint(3) UNSIGNED NOT NULL DEFAULT 80,
  `felicidad` tinyint(3) UNSIGNED NOT NULL DEFAULT 80,
  `edad` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `viva` tinyint(1) NOT NULL DEFAULT 1,
  `ultima_vez` timestamp NOT NULL DEFAULT current_timestamp(),
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mascota_memoria`
--

CREATE TABLE `mascota_memoria` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `clave` varchar(60) NOT NULL,
  `valor` varchar(200) NOT NULL,
  `guardado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mascota_objetos`
--

CREATE TABLE `mascota_objetos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pelota` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mascota_vinculos`
--

CREATE TABLE `mascota_vinculos` (
  `id` int(11) NOT NULL,
  `mascota_id_a` int(11) NOT NULL,
  `mascota_id_b` int(11) NOT NULL,
  `tipo` enum('amigos','pareja','enemigos') DEFAULT 'amigos',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
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

-- --------------------------------------------------------

--
-- Table structure for table `music_album_actions`
--

CREATE TABLE `music_album_actions` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `album_title` varchar(200) NOT NULL,
  `artist` varchar(200) NOT NULL DEFAULT '',
  `action_type` varchar(20) NOT NULL DEFAULT 'play',
  `yt_playlist_id` varchar(40) DEFAULT NULL,
  `spotify_album_id` varchar(40) DEFAULT NULL,
  `cover_url` varchar(500) DEFAULT NULL,
  `played_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `music_plays`
--

CREATE TABLE `music_plays` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` varchar(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `artist` varchar(200) NOT NULL DEFAULT '',
  `playlist_id` bigint(20) DEFAULT NULL,
  `duration_s` int(11) NOT NULL DEFAULT 0,
  `played_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `music_plays`
--

INSERT INTO `music_plays` (`id`, `user_id`, `video_id`, `title`, `artist`, `playlist_id`, `duration_s`, `played_at`) VALUES
(1, 2, 'hbuNxmAXdNk', 'Human', 'FLAVOR FOLEY', 13, 3, '2026-06-06 14:25:19'),
(2, 2, 'aHUVqV5CO6w', 'Children of the City', 'Mili', 13, 14, '2026-06-06 14:25:33'),
(3, 2, 'aHUVqV5CO6w', 'Children of the City', 'Mili', 13, 233, '2026-06-06 14:29:13'),
(4, 2, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 13, 144, '2026-06-06 14:31:39'),
(5, 2, 'RBhZieVCMPM', 'Between Two Worlds - Realm of Light', 'Mili', 13, 128, '2026-06-06 14:33:50'),
(6, 2, 'mAN5O-S3_6I', 'Between Two Worlds - Acapella', 'Mili', 13, 296, '2026-06-06 14:38:48'),
(7, 2, 'wVz13x2wC-E', 'Mortal With You - Japanese ver.', 'Mili', 13, 235, '2026-06-06 14:42:45'),
(8, 2, '-hEM_jVzWxM', 'Mortal With You - Instrumental', 'Mili', 13, 235, '2026-06-06 14:46:41'),
(9, 2, 'KSZp1gwcqtM', 'Bento Box Bivouac', 'Mili', 13, 252, '2026-06-06 14:50:56'),
(10, 2, 'XfTWgMgknpY', 'In Hell We Live, Lament (feat. KIHOW)', 'Mili;KIHOW', 13, 225, '2026-06-06 14:54:42'),
(11, 2, 't2mQxEtNgA0', 'Bulbel', 'Mili;ENDER LILIES', 13, 208, '2026-06-06 14:58:12'),
(12, 2, 'nN-FAKV2Zls', '雨と体液と匂い', 'Mili', 13, 296, '2026-06-06 15:03:09'),
(13, 2, 'ei7Kb6DoD_s', '雨と体液と匂い（instrumental）', 'Mili', 13, 299, '2026-06-06 15:08:09'),
(14, 2, '-UeABxedQhc', 'Static（instrumental）', 'Mili', 13, 210, '2026-06-06 15:11:41'),
(15, 2, '0cvCwHXgyeI', 'Space Colony', 'Mili', 13, 193, '2026-06-06 15:14:57'),
(16, 2, 'ESx_hy1n7HA', 'world.execute (me) ;', 'Mili', 13, 212, '2026-06-06 15:18:31'),
(17, 2, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 13, 24, '2026-06-07 17:30:44'),
(18, 2, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 13, 69, '2026-06-07 17:31:28'),
(19, 2, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 13, 144, '2026-06-07 17:32:45'),
(20, 2, 'RBhZieVCMPM', 'Between Two Worlds - Realm of Light', 'Mili', 13, 128, '2026-06-07 17:34:55'),
(21, 2, 'mAN5O-S3_6I', 'Between Two Worlds - Acapella', 'Mili', 13, 9, '2026-06-07 17:35:05'),
(22, 2, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 13, 61, '2026-06-07 17:36:25'),
(23, 2, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 13, 144, '2026-06-07 17:37:50'),
(24, 2, 'N5-h77JYOhE', 'Milk', 'Mili', 13, 197, '2026-06-07 17:41:09'),
(25, 2, '_UqGrwCbxVI', 'JUST BE COMPETENT', 'r u s s e l b u c k', 13, 42, '2026-06-07 17:41:52'),
(26, 2, 'ZuifkacZ0TA', 'Hero', 'Mili', 13, 214, '2026-06-07 17:45:27'),
(27, 2, 'yebNIHKAC4A', 'Golden', 'HUNTR/X;EJAE;AUDREY NUNA;REI AMI;KPop Demon Hunters Cast', 13, 138, '2026-06-07 17:47:45'),
(28, 2, '5-I1lT6Jbdo', 'Harpy Hare', 'Yaelokre', 13, 10, '2026-06-07 17:47:56'),
(29, 2, '5-I1lT6Jbdo', 'Harpy Hare', 'Yaelokre', 13, 179, '2026-06-07 17:50:46'),
(30, 2, 'k5mX3NkA7jM', 'Mary On A Cross', 'Ghost', 13, 244, '2026-06-07 17:54:53'),
(31, 2, '4o0WYiK52Dg', 'Body', 'Mother Mother', 13, 213, '2026-06-07 17:58:27'),
(32, 2, 'ukEE6OPltQA', 'A Complete and Utter Destruction of the Senses', 'Heaven Pierce Her', 13, 128, '2026-06-07 18:00:37'),
(33, 2, 'YCKfg6kHKTg', 'End It', 'RIELL', 13, 196, '2026-06-07 18:03:54'),
(34, 2, '3rSao4unXqc', 'Calculation Theme', 'Metric', 13, 211, '2026-06-07 18:07:28'),
(35, 2, 'RWX8NRIOf64', 'Opium', 'Mili', 13, 183, '2026-06-07 18:10:32'),
(36, 2, 'OTIgSuOI-i8', 'Running up that Hill (Nightcore)', 'Syrex', 13, 84, '2026-06-07 18:11:57'),
(37, 2, 'wqPdeT6Jpdg', 'Wet', 'Dazey and the Scouts', 13, 18, '2026-06-07 18:12:45'),
(38, 2, 'EiS7cKfuf6w', 'Sienna', 'The Marías', 13, 31, '2026-06-07 18:13:18'),
(39, 2, 'EiS7cKfuf6w', 'Sienna', 'The Marías', 13, 159, '2026-06-07 18:15:27'),
(40, 2, 'EiS7cKfuf6w', 'Sienna', 'The Marías', 13, 161, '2026-06-07 18:21:52'),
(41, 2, 'EiS7cKfuf6w', 'Sienna', 'The Marías', 13, 224, '2026-06-07 18:22:57'),
(42, 2, 'AS4q9yaWJkI', '砂の惑星 feat.初音ミク', 'hachi', 13, 39, '2026-06-07 18:23:36'),
(43, 2, 'AS4q9yaWJkI', '砂の惑星 feat.初音ミク', 'hachi', 13, 189, '2026-06-07 18:26:06'),
(44, 2, 'AS4q9yaWJkI', '砂の惑星 feat.初音ミク', 'hachi', 13, 239, '2026-06-07 18:26:57'),
(45, 2, '_QCzM4Eei9g', 'メズマライザー (feat. 初音ミク&重音テト)', '32ki;Hatsune Miku;重音テト', 13, 7, '2026-06-07 18:27:06'),
(46, 2, 'G_JfKOjwzwo', 'Through Patches of Violet', 'Mili', 13, 3, '2026-06-07 18:27:10');

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
(24, 2, 'review', 1, '{\"category\":\"music\",\"itemTitle\":\"Event Horizon (Reach for the Sun and Burn! Burn! Burn!)\",\"mtype\":\"song\"}', 1, '2026-05-29 22:35:17'),
(25, 2, 'review', 1, '{\"category\":\"music\",\"itemTitle\":\"FIRE!!!\",\"mtype\":\"song\"}', 1, '2026-05-29 22:35:28');

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
(12, 1, 'sdawdas', '2026-05-26 19:03:23'),
(13, 2, 'autismo', '2026-06-04 15:21:09');

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
(107, 12, 8, 'BvPrRkK1I6k', 'The Fall', 'Heaven Pierce Her', 187, 'Capi'),
(108, 13, 0, 'aHUVqV5CO6w', 'Children of the City', 'Mili', 234, 'Angie'),
(109, 13, 1, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 145, 'Angie'),
(110, 13, 2, 'RBhZieVCMPM', 'Between Two Worlds - Realm of Light', 'Mili', 129, 'Angie'),
(111, 13, 3, 'mAN5O-S3_6I', 'Between Two Worlds - Acapella', 'Mili', 297, 'Angie'),
(112, 13, 4, 'wVz13x2wC-E', 'Mortal With You - Japanese ver.', 'Mili', 236, 'Angie'),
(113, 13, 5, '-hEM_jVzWxM', 'Mortal With You - Instrumental', 'Mili', 235, 'Angie'),
(114, 13, 6, 'KSZp1gwcqtM', 'Bento Box Bivouac', 'Mili', 253, 'Angie'),
(115, 13, 7, 'XfTWgMgknpY', 'In Hell We Live, Lament (feat. KIHOW)', 'Mili;KIHOW', 225, 'Angie'),
(116, 13, 8, 't2mQxEtNgA0', 'Bulbel', 'Mili;ENDER LILIES', 208, 'Angie'),
(117, 13, 9, 'nN-FAKV2Zls', '雨と体液と匂い', 'Mili', 296, 'Angie'),
(118, 13, 10, 'ei7Kb6DoD_s', '雨と体液と匂い（instrumental）', 'Mili', 299, 'Angie'),
(119, 13, 11, '-UeABxedQhc', 'Static（instrumental）', 'Mili', 210, 'Angie'),
(120, 13, 12, '0cvCwHXgyeI', 'Space Colony', 'Mili', 194, 'Angie'),
(121, 13, 13, 'ESx_hy1n7HA', 'world.execute (me) ;', 'Mili', 212, 'Angie'),
(122, 13, 14, 'AN72_SVbETA', 'Utopiosphere -Platonism-', 'Mili', 236, 'Angie'),
(123, 13, 15, 'oHQUUAcB0io', 'Colorful', 'Mili', 241, 'Angie'),
(124, 13, 16, 'Le5nXTvNYJc', 'Nine Point Eight', 'Mili', 192, 'Angie'),
(125, 13, 17, '2P8laoe8jbU', 'Friction', 'Mili', 160, 'Angie'),
(126, 13, 18, 'AN72_SVbETA', 'YUBIKIRI-GENMAN', 'Mili', 236, 'Angie'),
(127, 13, 19, '8BxSROZU06M', 'Ephemeral', 'Mili', 266, 'Angie'),
(128, 13, 20, 'kpi3tCU2Clc', 'Fable', 'Mili', 202, 'Angie'),
(129, 13, 21, 'mkKGMY_zpg4', 'Maroma Samsa', 'Mili', 268, 'Angie'),
(130, 13, 22, 'zlKAAAW2NxU', 'Witch\'s Invitation', 'Mili', 305, 'Angie'),
(131, 13, 23, 'vjBFftpQxxM', 'BUTCHER VANITY', 'FLAVOR FOLEY', 186, 'Angie'),
(132, 13, 24, 'bnkjvpBigVY', 'Don', 'Miranda!;CA7RIEL', 197, 'Angie'),
(133, 13, 25, 'TGeOpZbBnbA', 'Birthday Kid - Key Ingredient ver.', 'Mili', 226, 'Angie'),
(134, 13, 26, 'gLRMHUGlp2w', 'Abnormality Dancing Girl', 'razaplays', 206, 'Angie'),
(135, 13, 27, '6Ro7NubzE9A', '1-800', 'bbno$;Ironmouse', 208, 'Angie'),
(136, 13, 28, 'fVE9rrbfCQ0', 'Blow My Brains Out', 'Tikkle Me', 222, 'Angie'),
(137, 13, 29, 'Kf_JjqzryG4', 'Verbatim', 'Mother Mother', 168, 'Angie'),
(138, 13, 30, 'dT5Ck6bXBu8', 'Don\'t', 'Azumi Takahashi;Lotus Juice;ATLUS Sound Team;ATLUS GAME MUSIC', 165, 'Angie'),
(139, 13, 31, 'klIxS5o65C4', 'ダイダイダイダイダイキライ', 'Amala', 155, 'Angie'),
(140, 13, 32, 'wZlv3qDPfjk', 'モエチャッカファイア', 'issey', 156, 'Angie'),
(141, 13, 33, '51GIxXFKbzk', 'INTERNET YAMERO', 'NEEDY GIRL OVERDOSE;KOTOKO;Aiobahn +81', 244, 'Angie'),
(142, 13, 34, 'ydXVSm24LC0', 'Don\'t Threaten Me with a Good Time', 'Panic! At The Disco', 211, 'Angie'),
(143, 13, 35, 'OyuyxJPO56c', 'Headfirst Slide Into Cooperstown On A Bad Bet', 'Fall Out Boy', 234, 'Angie'),
(144, 13, 36, 'LXuSOkf2M3c', 'Abracadabra', 'Lady Gaga', 224, 'Angie'),
(145, 13, 37, 'vp6XdbG3AhA', 'Pink Pony Club', 'Chappell Roan', 259, 'Angie'),
(146, 13, 38, 'w5OUAY1j3gQ', 'again', 'YUI', 258, 'Angie'),
(147, 13, 39, 'vD_D3zQ4Ais', 'The only sun light', 'Risa', 281, 'Angie'),
(148, 13, 40, 'PrSjlpWu0cw', 'Devil in Disguise', 'Marino', 104, 'Angie'),
(149, 13, 41, 'yebNIHKAC4A', 'Golden', 'HUNTR/X;EJAE;AUDREY NUNA;REI AMI;KPop Demon Hunters Cast', 199, 'Angie'),
(150, 13, 42, 'o8OjEIITii4', 'How It’s Done', 'HUNTR/X;EJAE;AUDREY NUNA;REI AMI;KPop Demon Hunters Cast', 176, 'Angie'),
(151, 13, 43, 'Ve_a9CXjlQc', 'Takedown', 'HUNTR/X;EJAE;AUDREY NUNA;REI AMI;KPop Demon Hunters Cast', 181, 'Angie'),
(152, 13, 44, 'S09qwKoSHbM', 'KICK BACK', 'Kenshi Yonezu', 194, 'Angie'),
(153, 13, 45, 'U_5S_tHezQE', 'check', 'bbno$', 122, 'Angie'),
(154, 13, 46, '5xc6lQzTUDA', 'Bad Apple!!', 'RichaadEB;Cristina Vee', 300, 'Angie'),
(155, 13, 47, 'bdP8Rtmz0t0', 'CandyCookieChocolate (feat. HATSUNE MIKU & KASANE TETO)', 'はろける;Hatsune Miku;Kasane Teto', 14, 'Angie'),
(156, 13, 48, 'JALbemLw3G4', 'Teto Territory', '2hot4tv', 208, 'Angie'),
(157, 13, 49, 'xPfMb50dsOk', 'Discord', 'The Living Tombstone;Eurobeat Brony', 194, 'Angie'),
(158, 13, 50, 'cqwcH_WIP-8', 'It\'s Going Down Now', 'Azumi Takahashi;Lotus Juice;ATLUS Sound Team;ATLUS GAME MUSIC', 183, 'Angie'),
(159, 13, 51, 'mdceoIcWwFw', '沈める街 - New Yoeko ver.', 'ヨエコ', 190, 'Angie'),
(160, 13, 52, 'dM7PrSrtrd4', 'Doin Time Speed', 'Ren', 23, 'Angie'),
(161, 13, 53, 'Ka1vNzmD6JE', 'Vampire Empire', 'Big Thief', 193, 'Angie'),
(162, 13, 54, 'VN06Chef3JM', 'FUKOUNA GIRL', 'STOMACH BOOK', 25, 'Angie'),
(163, 13, 55, 'r105CzDvoo0', 'Anytime Anywhere', 'milet', 261, 'Angie'),
(164, 13, 56, '-KE8NmtTlPk', 'After Midnight', 'Chappell Roan', 205, 'Angie'),
(165, 13, 57, 'olxdCY7hHEw', 'Coffee', 'Chappell Roan', 206, 'Angie'),
(166, 13, 58, 'kWLai0XoZmg', 'Super Graphic Ultra Modern Girl', 'Chappell Roan', 188, 'Angie'),
(167, 13, 59, '1mbbr-cJsrk', 'HOT TO GO!', 'Chappell Roan', 185, 'Angie'),
(168, 13, 60, '7hVIf9YD6Vk', 'My Kink Is Karma', 'Chappell Roan', 223, 'Angie'),
(169, 13, 61, 'EqUHnojFH5Y', 'Picture You', 'Chappell Roan', 187, 'Angie'),
(170, 13, 62, 'E9sngJmXijQ', 'Kaleidoscope', 'Chappell Roan', 223, 'Angie'),
(171, 13, 63, 'vp6XdbG3AhA', 'Pink Pony Club', 'Chappell Roan', 259, 'Angie'),
(172, 13, 64, 'GcXlN7meclE', 'California', 'Chappell Roan', 211, 'Angie'),
(173, 13, 65, 'xjBKcdFU3Hs', 'Guilty Pleasure', 'Chappell Roan', 225, 'Angie'),
(174, 13, 66, 'Qam5A9lG-wc', 'The Subway', 'Chappell Roan', 252, 'Angie'),
(175, 13, 67, 'vvLxo7h12PQ', 'Kick Back from Chainsaw Man', 'Kotoband;Piper', 86, 'Angie'),
(176, 13, 68, 'HqemAG6hTvQ', 'Idol from Oshi no Ko', 'Kotoband;Misty M.', 213, 'Angie'),
(177, 13, 69, 'L9uj2XChyBI', 'Headlock', 'Imogen Heap', 216, 'Angie'),
(178, 13, 70, 'XWX_j3b9ZeE', 'two', 'bbno$', 137, 'Angie'),
(179, 13, 71, 'gP9PkttPC10', 'Promised Land from Pichi Pichi Pitch', 'Kotoband;Misty M.', 104, 'Angie'),
(180, 13, 72, 'uHTZ1BmK6KA', 'Secret Base from AnoHana', 'Kotoband;Misty M.;Hannah B.;Ariadna G.', 180, 'Angie'),
(181, 13, 73, 'zVrHCm58N-o', 'To Your Oblivion', 'Mili', 262, 'Angie'),
(182, 13, 74, 'wt4af_R6iJk', 'Duvet', 'bôa', 204, 'Angie'),
(183, 13, 75, '0oad1M3SpzI', 'Butcher Vanity - Cover Español', 'Miree', 191, 'Angie'),
(184, 13, 76, 'k5mX3NkA7jM', 'Mary On A Cross', 'Ghost', 245, 'Angie'),
(185, 13, 77, 'wLaDksDOcE4', 'Anthems For A Seventeen Year-Old Girl', 'Broken Social Scene', 271, 'Angie'),
(186, 13, 78, '3rSao4unXqc', 'Calculation Theme', 'Metric', 212, 'Angie'),
(187, 13, 79, 'nmbiBVPe5bY', 'Strategy', 'TWICE', 167, 'Angie'),
(188, 13, 80, 'pAIkCINLMHA', 'Bathroom Bitch', 'HOLYCHILD', 176, 'Angie'),
(189, 13, 81, 'dOrszUZS-M8', 'I Like You Best', 'Ella Red', 163, 'Angie'),
(190, 13, 82, '_UqGrwCbxVI', 'JUST BE COMPETENT', 'r u s s e l b u c k', 157, 'Angie'),
(191, 13, 83, '9OMh9iZEhwI', 'Smile', 'Dami Im', 183, 'Angie'),
(192, 13, 84, 'bDpi8EdPMhU', 'JOYRIDE.', 'Kesha', 150, 'Angie'),
(193, 13, 85, 'b2Bb4FT1LdE', 'Unavailable (Demo Version)', 'Mike Adams;Vincent Russo', 195, 'Angie'),
(194, 13, 86, 'ViLOklZmQCI', 'favorite apple', 'The Two Lips', 149, 'Angie'),
(195, 13, 87, 'edirMh-BzY4', 'Ripples of Past Reverie - English Ver.', 'HOYO-MiX;Cassie Wei', 187, 'Angie'),
(196, 13, 88, 'cVL4bRjA6X0', 'Peach Pit and Cyanide', 'Mili', 199, 'Angie'),
(197, 13, 89, 'zsBBWBEZkFQ', 'MIRROR', 'Ado', 180, 'Angie'),
(198, 13, 90, 'Jx7nt0qeLF8', 'What the Ripple Sees', 'Mili', 259, 'Angie'),
(199, 13, 91, 'dnPe8dSPyWw', 'Cubibibibism', 'OMGkawaiiAngel;NEEDY GIRL OVERDOSE;Haraguchi Sasuke', 204, 'Angie'),
(200, 13, 92, 'mdceoIcWwFw', 'Shizumeru-Machi (Sinking Town) - New Yoeko ver.', 'Yoeko', 190, 'Angie'),
(201, 13, 93, 'KJ402ScoUhc', 'Sinking Town', 'Dev1lHawk;Dev1lCat;xaviorthemachine', 179, 'Angie'),
(202, 13, 94, 'KlTNKOnfXFk', 'Static', 'FLAVOR FOLEY', 244, 'Angie'),
(203, 13, 95, 'uWMr16O_Aso', 'Teto the 31st (feat. KASANE TETO & Ui)', 'はろける;Kasane Teto;Ui', 139, 'Angie'),
(204, 13, 96, 'SzkoFKIN50I', 'For the Record', 'Jonathan Groff;Jessie Shelton;36 Questions', 304, 'Angie'),
(205, 13, 97, 'Zqk3eX4j_qc', 'Neon', 'ONE OK ROCK', 185, 'Angie'),
(206, 13, 98, 'FhksmAd0O2w', 'Help I\'m Alive', 'Metric', 286, 'Angie'),
(207, 13, 99, '2CV6aMhpTQs', 'POP IN 2 - ルビー Solo Ver.-', 'B小町;ルビー(CV:伊駒ゆりえ)', 267, 'Angie'),
(208, 13, 100, 'pHkLpHayna0', 'SPAGHETTI', 'LE SSERAFIM;j-hope', 172, 'Angie'),
(209, 13, 101, 'h0djuhl97Kw', 'SAIKAI', 'Mili', 324, 'Angie'),
(210, 13, 102, '6f1-QF9jvBM', 'Anybody Have a Map?', 'Rachel Bay Jones;Jennifer Laura Thompson', 147, 'Angie'),
(211, 13, 103, 'kfnMvo87fQU', 'Waving Through A Window', 'Ben Platt;Original Broadway Cast of Dear Evan Hansen', 236, 'Angie'),
(212, 13, 104, 'xkdPRcY0k4o', 'For Forever', 'Ben Platt', 302, 'Angie'),
(213, 13, 105, '7F6e-dFQHxI', 'Requiem', 'Laura Dreyfuss;Michael Park;Jennifer Laura Thompson', 313, 'Angie'),
(214, 13, 106, 'X1JpwegsMAM', 'If I Could Tell Her', 'Ben Platt;Laura Dreyfuss', 249, 'Angie'),
(215, 13, 107, 's1Evnzkez7o', 'Only Us', 'Laura Dreyfuss;Ben Platt', 224, 'Angie'),
(216, 13, 108, 'XXvHUqR0X1s', 'Good For You', 'Rachel Bay Jones;Kristolyn Lloyd;Will Roland;Ben Platt', 185, 'Angie'),
(217, 13, 109, 'XCf-xT7hUsE', 'Fight for Me', 'Barrett Wilbert Weed', 155, 'Angie'),
(218, 13, 110, 'icBDYkfxpMs', 'Looping the Rooms (feat. HATSUNE MIKU)', 'rusino;Hatsune Miku', 134, 'Angie'),
(219, 13, 111, 'k4MMkrXLX2g', 'Would You Fall in Love with Me Again', 'Jorge Rivera-Herrans;Anna Lea', 346, 'Angie'),
(220, 13, 112, 'oB8lqgO9e24', 'Warrior of the Mind', 'Jorge Rivera-Herrans;Teagan Earley;Cast of EPIC: The Musical', 208, 'Angie'),
(221, 13, 113, 'hbuNxmAXdNk', 'Human', 'FLAVOR FOLEY', 268, 'Angie'),
(222, 13, 114, 'EpbhsrYuQJ4', 'Queen of Venus', 'FLAVOR FOLEY', 268, 'Angie'),
(223, 13, 115, '9x19_Pjgcvc', 'Remember', 'yuigot;Yachiyo Runami(cv.Saori Hayami)', 233, 'Angie'),
(224, 13, 116, '4BCUQVaYTbQ', 'Starry Sea', 'Aqu3ra;Yachiyo Runami(cv.Saori Hayami)', 253, 'Angie'),
(225, 13, 117, 'mgoCQfFqIVk', 'Watashiwa Watashino Kotoga Suki', 'HoneyWorks;Kaguya(cv.Yuko Natsuyoshi)', 251, 'Angie'),
(226, 13, 118, 'laOwIwY_dWg', 'World is Mine - Kaguya&Yachiyo Runami ver. - CPK! Remix', 'ryo (supercell);Kaguya(cv.Yuko Natsuyoshi);Yachiyo Runami(cv.Saori Hayami)', 297, 'Angie'),
(227, 13, 119, 'HvgX44ESvHQ', 'I\'m Your Man', 'Mitski', 210, 'Angie'),
(228, 13, 120, '2ROnuyg_YbA', 'He\'s My Man', 'Luvcat', 233, 'Angie'),
(229, 13, 121, '3mvB60E8Hto', 'Melt - Kaguya ver. - CPK! Remix', 'ryo (supercell);Kaguya(cv.Yuko Natsuyoshi)', 265, 'Angie'),
(230, 13, 122, '075raB27CW8', 'ray - Cosmic Princess Kaguya! Version', 'Kaguya(cv.Yuko Natsuyoshi);Yachiyo Runami(cv.Saori Hayami);TAKU INOUE', 302, 'Angie'),
(231, 13, 123, 'jsqH5MWc0RE', 'Reply', 'kz;Kaguya(cv.Yuko Natsuyoshi)', 308, 'Angie'),
(232, 13, 124, 'h9c0gegwcM0', 'A Symphony of Moments', '40mP;Kaguya(cv.Yuko Natsuyoshi)', 263, 'Angie'),
(233, 13, 125, 'IFMLSODxS5U', 'Happy Synthesizer - Cover', 'Kaguya(cv.Yuko Natsuyoshi);yuigot', 105, 'Angie'),
(234, 13, 126, 'kjQCuv3vHbw', 'Ex-Otogibanashi', 'ryo (supercell);Kaguya(cv.Yuko Natsuyoshi);Yachiyo Runami(cv.Saori Hayami)', 219, 'Angie'),
(235, 13, 127, 'EiS7cKfuf6w', 'Sienna', 'The Marías', 225, 'Angie'),
(236, 13, 128, 'pEnGrOGNa8A', 'Loser', 'Sunday Cruise', 195, 'Angie'),
(237, 13, 129, 'wqPdeT6Jpdg', 'Wet', 'Dazey and the Scouts', 172, 'Angie'),
(238, 13, 130, 'nbcCG7PkI18', 'Shut Up and Dance', 'WALK THE MOON', 199, 'Angie'),
(239, 13, 131, 'xO_12-FBrMk', 'Too Little, Too Late', 'Laufey', 234, 'Angie'),
(240, 13, 132, '84UfQLzYWws', 'String Theory', 'vally.exe;SoundCirclet', 219, 'Angie'),
(241, 13, 133, 'acnx9QFbAp4', 'TIE HUA FEI', 'Mili;Monster Siren Records', 262, 'Angie'),
(242, 13, 134, 'YTspU_WM6rE', 'Doin Time - Sped Up', 'Hiko', 181, 'Angie'),
(243, 13, 135, '5-I1lT6Jbdo', 'Harpy Hare', 'Yaelokre', 180, 'Angie'),
(244, 13, 136, 'Pj6ntDEEfeE', 'The Red Means I Love You', 'Madds Buckley', 238, 'Angie'),
(245, 13, 137, 'Jr-vAwJLUJc', 'Wait a Minute! (Sped Up) - I Think I Left My Consciousness in the 6Th Dimension', 'Hiko', 116, 'Angie'),
(246, 13, 138, '-PgImeQfrEs', 'The Lighthouse', 'Halsey', 273, 'Angie'),
(247, 13, 139, 'UZton86SuOg', 'Material Girl', 'Madonna', 233, 'Angie'),
(248, 13, 140, 'jzHtHAg2igc', 'Telepathy', 'BTS', 202, 'Angie'),
(249, 13, 141, 'xUjpCR3qiz0', 'Porque te vas', 'Jeanette', 205, 'Angie'),
(250, 13, 142, 'HcYN5Gn5IuM', 'コネクト', 'ClariS', 272, 'Angie'),
(251, 13, 143, '_sOKkON_UnQ', '4:00A.M.', 'Taeko Onuki', 337, 'Angie'),
(252, 13, 144, 'qNORzJtsohg', 'Running up that Hill (Sped Up)', 'Sped Up Mage;Syrex', 108, 'Angie'),
(253, 13, 145, 'BGztdO-GWsw', 'The Moon Will Sing', 'The Crane Wives', 219, 'Angie'),
(254, 13, 146, 'mZscJ1YkGR0', 'Would You Fall in Love with Me Again', 'Annapantsu;Chloe Breez', 321, 'Angie'),
(255, 13, 147, 'LmZD-TU96q4', 'IRIS OUT', 'Kenshi Yonezu', 153, 'Angie'),
(256, 13, 148, 'o6flxGrbmCw', 'Fame is a Gun', 'Addison Rae', 183, 'Angie'),
(257, 13, 149, 'PFioWmokVgc', 'One Way Or Another - Remastered 2001', 'Blondie', 215, 'Angie'),
(258, 13, 150, 'R1Ch963iuek', 'Until Our Sky Is Blue', 'Mili', 236, 'Angie'),
(259, 13, 151, '8LvAiJYKoSM', 'Hayloft', 'Mother Mother', 182, 'Angie'),
(260, 13, 152, 'x6yGHOpIe5c', 'Burning Pile', 'Mother Mother', 262, 'Angie'),
(261, 13, 153, 'LTEZm5AYslw', 'Hayloft II', 'Mother Mother', 215, 'Angie'),
(262, 13, 154, 'nXHoSuUhDEc', 'Arms Tonite', 'Mother Mother', 217, 'Angie'),
(263, 13, 155, '4o0WYiK52Dg', 'Body', 'Mother Mother', 214, 'Angie'),
(264, 13, 156, 'Dao5P8Mqkzw', 'Wrecking Ball', 'Mother Mother', 194, 'Angie'),
(265, 13, 157, 'artn9fErRp8', 'Problems', 'Mother Mother', 208, 'Angie'),
(266, 13, 158, 'GwSSrwryxN0', 'Impostor Syndrome', 'Sidney Gish', 294, 'Angie'),
(267, 13, 159, 'isZbEoAzvLg', 'Dr. Sunshine Is Dead', 'Will Wood and the Tapeworms', 287, 'Angie'),
(268, 13, 160, '8Bu3N-2tA_0', 'Impacto', 'Enjambre;Denise Gutiérrez', 238, 'Angie'),
(269, 13, 161, 'SgnSMftcFN0', 'I / Me / Myself', 'Will Wood', 292, 'Angie'),
(270, 13, 162, 'WuzIw73pmSc', 'My Ordinary Life', 'The Living Tombstone', 231, 'Angie'),
(271, 13, 163, '1v9q8piZnLc', 'I Can\'t Fix You (feat. Crusher-P)', 'The Living Tombstone;Crusher-P', 279, 'Angie'),
(272, 13, 164, 'a2jGt3VMlcM', 'The Hand', 'Annabelle Dinda', 191, 'Angie'),
(273, 13, 165, '6HXuC_HCNjw', 'Savages', 'That Handsome Devil', 289, 'Angie'),
(274, 13, 166, 'WIKqgE4BwAY', 'Gimme Chocolate!!', 'BABYMETAL', 243, 'Angie'),
(275, 13, 167, '0iVlSNpq8i8', 'BIRDBRAIN', 'Jamie Paige;OK Glass', 256, 'Angie'),
(276, 13, 168, 'tPgngMS9Yi0', 'My Clematis (VIVINOS - ALNST Original Soundtrack Part.1)', 'Rubyeye', 232, 'Angie'),
(277, 13, 169, 'eSW2LVbPThw', 'ラビットホール', 'DECO*27', 162, 'Angie'),
(278, 13, 170, 'o3Z2k-bhoUo', 'Blink Gone (VIVINOS - ALNST Original Soundtrack Part.8)', 'BL8M;AKUGETSU', 189, 'Angie'),
(279, 13, 171, 'x1UsJ2Znjk0', 'Crime And Punishment', 'Ado', 290, 'Angie'),
(280, 13, 172, 'LaEgpNBt-bQ', 'M@GICAL CURE! LOVE SHOT! (feat. Hatsune Miku)', 'SAWTOWNE;Hatsune Miku', 217, 'Angie'),
(281, 13, 173, '1-PMBxtN4N0', 'Black Sorrow (VIVINOS - ALNST Original Soundtrack Part.4)', 'PARK BYEONG HOON', 194, 'Angie'),
(282, 13, 174, 'WvKd91KwTKM', 'Life We Sow', '魔法使いの約束;Mili', 209, 'Angie'),
(283, 13, 175, 'L8Jtgj8j4tY', 'Skin-Deep Comedy', '魔法使いの約束;Mili', 322, 'Angie'),
(284, 13, 176, 'emVNCcwCtuc', 'I Am a Fluff (360 Reality Audio)', 'Mili', 267, 'Angie'),
(285, 13, 177, 'Sc3SQyM-Gvg', 'Sideshow Duckling', 'Mili', 230, 'Angie'),
(286, 13, 178, '3UJ_mERvw3A', 'Within (Goblin Slayer Episode Twelve inserted song)', 'Mili', 164, 'Angie'),
(287, 13, 179, 'nf5faU1fh1M', 'Excalibur', 'Mili', 178, 'Angie'),
(288, 13, 180, '-vlEd1Pbdxk', 'world.search (you) ;', 'Mili', 295, 'Angie'),
(289, 13, 181, '6DZjCgxbx5U', 'Rubber Human', 'Mili', 144, 'Angie'),
(290, 13, 182, 'cbM2ywsJZIo', 'Pass on (Vocal. Gregor)', 'ProjectMoon', 196, 'Angie'),
(291, 13, 183, 'zD037KtLniI', 'A Turtle\'s Heart - Key Ingredient ver.', 'Mili', 175, 'Angie'),
(292, 13, 184, 'ESx_hy1n7HA', 'world.execute(me); - Key Ingredient ver.', 'Mili', 212, 'Angie'),
(293, 13, 185, '0r2GNWvkd4U', 'Iron Lotus - Key Ingredient ver.', 'Mili', 228, 'Angie'),
(294, 13, 186, '2wSY55e3GxI', 'RTRT - Key Ingredient ver.', 'Mili', 209, 'Angie'),
(295, 13, 187, 'GIA3V_buzI4', 'TOKYO NEON - Key Ingredient ver.', 'Mili', 253, 'Angie'),
(296, 13, 188, 'a2O-UrADXwE', 'Rubber Human - Key Ingredient ver.', 'Mili', 148, 'Angie'),
(297, 13, 189, '8i0UdD-xSRw', 'String Theocracy - Key Ingredient ver.', 'Mili', 170, 'Angie'),
(298, 13, 190, 'deQIVqwPiE8', 'Ga1ahad and Scientific Witchery - Key Ingredient ver.', 'Mili', 223, 'Angie'),
(299, 13, 191, 'v2nI3ZMvRCg', 'Lemonade - Key Ingredient ver.', 'Mili', 171, 'Angie'),
(300, 13, 192, 'DBBCZWkVZLI', 'Summoning 101 - Key Ingredient ver.', 'Mili', 174, 'Angie'),
(301, 13, 193, 'ZlyVDiX8VdQ', 'From a Place of Love - Key Ingredient ver.', 'Mili', 205, 'Angie'),
(302, 13, 194, 'BQsUJfR58X0', 'Chocological - Key Ingredient ver.', 'Mili', 241, 'Angie'),
(303, 13, 195, 'ctiZlAhQmkw', 'Main Theme', 'Binary Haze Interactive;Mili', 65, 'Angie'),
(304, 13, 196, 'RMRK2A6bXrs', 'Harmonious', 'Binary Haze Interactive;Mili', 188, 'Angie'),
(305, 13, 197, 'Le5nXTvNYJc', 'Nine Point Eight', 'Mili', 192, 'Angie'),
(306, 13, 198, 'KGJ02115vNg', 'Holy and Darkness 1', 'arai tasuku;Mili', 599, 'Angie'),
(307, 13, 199, 'JHY0PYZXvfU', 'sustain++;', 'Mili', 361, 'Angie'),
(308, 13, 200, 'JHY0PYZXvfU', 'sustain++;（ending ver.）～『攻殻機動隊 SAC_2045』エンディングテーマ～', 'Mili', 361, 'Angie'),
(309, 13, 201, 'qfDhiBUNzwA', 'Sloth', 'Mili', 174, 'Angie'),
(310, 13, 202, 'lGIXSbT5FvY', 'Mob Mentality', 'Mili', 62, 'Angie'),
(311, 13, 203, 'JdcHwbZ-l7Q', 'Though Our Paths May Diverge (Goblin Slayer Episode Seven inserted song)', 'Mili', 199, 'Angie'),
(312, 13, 204, 'OboGTtdOUfw', 'Ocean Bby', 'Mili', 266, 'Angie'),
(313, 13, 205, 'HrkFQAQyFGc', '雨と体液と匂い（ending ver.）～TVアニメ『グレイプニル』エンディングテーマ～', 'Mili', 299, 'Angie'),
(314, 13, 206, 'bYIS0jAWOss', 'Monsters in the Woods', '魔法使いの約束;Mili', 221, 'Angie'),
(315, 13, 207, 'u3k43z6fiMk', 'Gluttony', '魔法使いの約束;Mili', 220, 'Angie'),
(316, 13, 208, 'z_byIUR43to', 'Whiteout', '魔法使いの約束;Mili', 213, 'Angie'),
(317, 13, 209, 'jP1I33ic_YI', 'Main theme (feat. Cassie Wei)', 'Mili;Yamato Kasai;Cassie Wei', 144, 'Angie'),
(318, 13, 210, 'z3Rz1BLk8Hc', 'Symbiosis (feat. Cassie Wei)', 'Mili;Yamato Kasai;Cassie Wei', 155, 'Angie'),
(319, 13, 211, 'ATfX1e50v94', 'Dignity (feat. Cassie Wei)', 'Mili;Yamato Kasai;Cassie Wei', 137, 'Angie'),
(320, 13, 212, 'kbj7CoHYcP0', 'Lily tree (feat. Cassie Wei)', 'Mili;Yamato Kasai;Cassie Wei', 197, 'Angie'),
(321, 13, 213, 'bI3542HJRzY', 'CandyCookieChocolate', 'はろける', 169, 'Angie'),
(322, 13, 214, 'THRtKGX-czY', 'An Unhealthy Obsession', 'The Blake Robinson Synthetic Orchestra', 193, 'Angie'),
(323, 13, 215, 'fVE9rrbfCQ0', 'Blow My Brains Out', 'Tikkle Me', 222, 'Angie'),
(324, 13, 216, 'vjBFftpQxxM', 'Butcher Vanity', 'Vane Lily;Jamie Paige;ricedeity', 186, 'Angie'),
(325, 13, 217, '1xEfMnXyGkA', 'Language of the Lost', 'Riproducer', 251, 'Angie'),
(326, 13, 218, 'BW5G7v5PqPc', 'Writing on the Wall', 'Will Stetson', 276, 'Angie'),
(327, 13, 219, '0NCnDwv8_oE', 'Therefor you and me', 'si-o', 20, 'Angie'),
(328, 13, 220, 'QFlCSMlpQIQ', 'The Vampire', 'Rachie', 195, 'Angie'),
(329, 13, 221, '0T--URl-g_4', 'Writing On The Wall - ver. Alhaitham', 'kanalia', 271, 'Angie'),
(330, 13, 222, '5NarVgDFNX0', 'アイドル', 'YOASOBI', 226, 'Angie'),
(331, 13, 223, 'ESx_hy1n7HA', 'world.execute (me) ;', 'Mili', 212, 'Angie'),
(332, 13, 224, 'Y2izxUyWjTU', 'Doin Time (Sped Up) - Evil, I\'ve Come to Tell You That She\'s Evil, Most Definitely', 'Hiko', 181, 'Angie'),
(333, 13, 225, 'IcpzqZrpLVM', 'RTRT', 'Mili', 215, 'Angie'),
(334, 13, 226, 'CocEMWdc7Ck', 'Shakira: Bzrp Music Sessions, Vol. 53/66', 'Bizarrap;Shakira', 218, 'Angie'),
(335, 13, 227, 'wkJxbV1ZlE0', 'Rosa Pastel', 'Belanova', 186, 'Angie'),
(336, 13, 228, '7mW4FUe_ySc', 'GOSSIP (feat. Tom Morello)', 'Måneskin;Tom Morello', 168, 'Angie'),
(337, 13, 229, 'wvz97-lNPH8', 'Villano Antillano: Bzrp Music Sessions, Vol. 51/66', 'Bizarrap;Villano Antillano', 188, 'Angie'),
(338, 13, 230, 'RtTYQuO1j6w', 'Necromantic', 'Akatsuki Records', 275, 'Angie'),
(339, 13, 231, 'bnkjvpBigVY', 'Don', 'Miranda!;CA7RIEL', 197, 'Angie'),
(340, 13, 232, 'wBivPsGuz7Y', 'Unholy', 'Lollia;Sleeping Forest', 164, 'Angie'),
(341, 13, 233, 'XfTWgMgknpY', 'In Hell We Live, Lament (feat. KIHOW)', 'Mili;KIHOW', 225, 'Angie'),
(342, 13, 234, 'd-nxW9qBtxQ', 'Ga1ahad and Scientific Witchery', 'Mili', 219, 'Angie'),
(343, 13, 235, 'x6q41EnhPnU', 'Summoning 101', 'Mili', 176, 'Angie'),
(344, 13, 236, '-DHcjEVm-l4', 'Gunners in the Rain', 'Mili', 228, 'Angie'),
(345, 13, 237, 'Ly8QIZ0vZYE', 'Mushrooms', 'Mili', 230, 'Angie'),
(346, 13, 238, 'WpE98Jn6dAY', 'Sl0t', 'Mili', 290, 'Angie'),
(347, 13, 239, 'Q2XJNYVOaok', 'Extension of You', 'Mili', 292, 'Angie'),
(348, 13, 240, 'VkdrrxR96d8', 'Mirror Mirror', 'Mili', 197, 'Angie'),
(349, 13, 241, 'zlKAAAW2NxU', 'Witch\'s Invitation', 'Mili', 305, 'Angie'),
(350, 13, 242, '_V17JN76uxc', 'Ancient Dreams in a Modern Land', 'MARINA', 204, 'Angie'),
(351, 13, 243, 'Ie0Ub3-Dx8Y', 'Teen Idle', 'MARINA', 254, 'Angie'),
(352, 13, 244, 'F1JTlnHGa90', 'Venus Fly Trap', 'MARINA', 182, 'Angie'),
(353, 13, 245, 'Ks3YoKqJnfI', 'Romeo and Cinderella', 'Rachie;PalmMute', 298, 'Angie'),
(354, 13, 246, 'KoV4kKMwz5k', 'Birth of a new witch (Full Size) [feat. Zakuro Motoki]', 'Luck Ganriki;Zakuro Motoki', 300, 'Angie'),
(355, 13, 247, 'j0gDBT_Kow4', 'Birthday Kid', 'Mili', 213, 'Angie'),
(356, 13, 248, 'RbYva7AE8Aw', 'Victim', 'Mili', 231, 'Angie'),
(357, 13, 249, 'dSx77g4yIek', 'ヒーロー', 'supercell', 312, 'Angie'),
(358, 13, 250, 'wVGbab8tpRA', '星が瞬くこんな夜に 〜ゲームVer.〜', 'supercell', 267, 'Angie'),
(359, 13, 251, 'tzmpAC7ddPo', 'Zydrate Anatomy', 'Paris Hilton;Alexa Vega;Terrance Zdunich', 204, 'Angie'),
(360, 13, 252, '_PSjoVXFGAQ', 'Fly, My Wings', 'Mili', 195, 'Angie'),
(361, 13, 253, 'bJieaH23524', 'Mortal With You', 'Mili', 233, 'Angie'),
(362, 13, 254, 'nOj_A3aZxGs', 'String Theocracy', 'Mili', 175, 'Angie'),
(363, 13, 255, 'xyx8DMlUAQ4', 'From a Place of Love', 'Mili', 182, 'Angie'),
(364, 13, 256, 'lVLXJTubd9w', 'And Then is Heard No More', 'Mili', 177, 'Angie'),
(365, 13, 257, 'In5Du5x6MZM', 'Iron Lotus', 'Mili', 240, 'Angie'),
(366, 13, 258, 'aHUVqV5CO6w', 'Children of the City', 'Mili', 234, 'Angie'),
(367, 13, 259, 'qh7CFsnfdpk', 'Gone Angels', 'Mili', 145, 'Angie'),
(368, 13, 260, 'UqUH7LHMj50', 'Poems of a Machine', 'Mili', 274, 'Angie'),
(369, 13, 261, 'Dca9gJyjoAg', 'Salt, Pepper, Birds, and the Thought Police', 'Mili', 244, 'Angie'),
(370, 13, 262, 'v-zLO_cwijs', 'Red Dahlia', 'Mili', 138, 'Angie'),
(371, 13, 263, 'S77Dfzzyf-c', 'Unidentified Flavourful Object', 'Mili', 245, 'Angie'),
(372, 13, 264, 'NPoYb4mbiOg', 'Meatball Submarine', 'Mili', 192, 'Angie'),
(373, 13, 265, 'p7sNIyP14X8', 'Vulnerability', 'Mili', 129, 'Angie'),
(374, 13, 266, 'x0b9_Aq3o-Q', 'NENTEN', 'Mili', 190, 'Angie'),
(375, 13, 267, '-n-iqXTztVQ', 'Bathtub Mermaid', 'Mili', 225, 'Angie'),
(376, 13, 268, 'uGYk8nfIuKw', 'Cerebrite', 'Mili', 162, 'Angie'),
(377, 13, 269, '0cvCwHXgyeI', 'Space Colony', 'Mili', 194, 'Angie'),
(378, 13, 270, 'AN72_SVbETA', 'Utopiosphere -Platonism-', 'Mili', 236, 'Angie'),
(379, 13, 271, 'Lbn3q0qe16Q', 'Painful Death for the Lactose Intolerant', 'Mili', 127, 'Angie'),
(380, 13, 272, 'VkhEnvIy0yU', 'YUBIKIRI-GENMAN - special edit', 'Mili', 235, 'Angie'),
(381, 13, 273, 'oOlWu15vzyE', 'Past the Stargazing Season', 'Mili', 295, 'Angie'),
(382, 13, 274, 'oHQUUAcB0io', 'Colorful', 'Mili', 241, 'Angie'),
(383, 13, 275, 'shC5MDtYpBU', 'Boys in Kaleidosphere', 'Mili', 94, 'Angie'),
(384, 13, 276, 'tBYU9W1ezL0', 'Camelia', 'Mili', 282, 'Angie'),
(385, 13, 277, 'sA5xYUGet2g', 'Vitamins (feat. world\'s end girlfriend)', 'Mili;World\'s End Girlfriend', 300, 'Angie'),
(386, 13, 278, '_-9YVWH6YZI', 'Lemonade', 'Mili', 194, 'Angie'),
(387, 13, 279, 'N5-h77JYOhE', 'Milk', 'Mili', 197, 'Angie'),
(388, 13, 280, '-vlEd1Pbdxk', 'world.search (you) ;', 'Mili', 295, 'Angie'),
(389, 13, 281, 'egSj8UlNjf4', 'Gertrauda', 'Mili', 126, 'Angie'),
(390, 13, 282, 'Zj3-UToODGE', 'TOKYO NEON', 'Mili', 255, 'Angie'),
(391, 13, 283, '7zj5wfAjF2Q', 'With a Billion Worldful of ', 'Mili;DE DE MOUSE', 205, 'Angie'),
(392, 13, 284, '1qgOvHut_40', 'Every Other Ghost', 'Mili', 203, 'Angie'),
(393, 13, 285, '0mzDohtbsdM', 'Fossil', 'Mili', 214, 'Angie'),
(394, 13, 286, '6DZjCgxbx5U', 'Rubber Human', 'Mili', 144, 'Angie'),
(395, 13, 287, 'nf5faU1fh1M', 'Excalibur', 'Mili', 178, 'Angie'),
(396, 13, 288, '_8cMjlCnzZQ', 'Let the Maggots Sing', 'Mili', 267, 'Angie'),
(397, 13, 289, 'LxduFWbbndE', 'Nine Point Eight -special edit-', 'Mili', 241, 'Angie'),
(398, 13, 290, '1Hsj3oYhsbk', 'Sleep Talk Metropolis', 'Mili', 243, 'Angie'),
(399, 13, 291, 'KMaQhPLCqWY', 'Purgatorium', 'お月さま交響曲', 151, 'Angie'),
(400, 13, 292, 'oPgsK0oKfks', 'LUVORATORRRRRY!', 'Reol', 204, 'Angie'),
(401, 13, 293, '7aJYvuiOZac', 'ワンダーランド地下', '香椎モイミ', 155, 'Angie'),
(402, 13, 294, 'XFPdJM8YQoA', 'Abnormality Dancin\' Girl', 'MICCHI;Drazically', 208, 'Angie'),
(403, 13, 295, '7gmGYDxlg20', 'Six Feet Under', 'Vane Lily', 206, 'Angie'),
(404, 13, 296, '-H2PCK7DJsQ', 'サラマンダー', 'DECO*27', 157, 'Angie'),
(405, 13, 297, 'AS4q9yaWJkI', '砂の惑星 feat.初音ミク', 'hachi', 239, 'Angie'),
(406, 13, 298, 'dy90tA3TT1c', 'Monster', 'YOASOBI', 208, 'Angie'),
(407, 13, 299, 'yZkPe7TEuyk', 'FIRE!!!', 'Vane Lily;Jamie Paige', 261, 'Angie'),
(408, 13, 300, 'dSw8CucthGc', 'meltdown', 'iroha(sasaki)', 336, 'Angie'),
(409, 13, 301, 'SZcilh-cZXE', 'ワールドイズマイン-初音ミク「マジカルミライ 2021」Live- (feat. 初音ミク)', 'ryo (supercell);Hatsune Miku', 254, 'Angie'),
(410, 13, 302, 'Ka1vNzmD6JE', 'Vampire Empire', 'Big Thief', 193, 'Angie'),
(411, 13, 303, '5e4INH1yr9c', 'Nothing\'s New', 'Rio Romeo', 209, 'Angie'),
(412, 13, 304, 'GHWfTwnBA_M', '痛いの痛いの飛んでいけ', 'TOOBOE', 60, 'Angie'),
(413, 13, 305, 'wZlv3qDPfjk', 'MoeChakkaFire', 'issey', 156, 'Angie'),
(414, 13, 306, 'dT5Ck6bXBu8', 'Don\'t', 'Azumi Takahashi;Lotus Juice;ATLUS Sound Team;ATLUS GAME MUSIC', 165, 'Angie'),
(415, 13, 307, '5-I1lT6Jbdo', 'Harpy Hare', 'Yaelokre', 180, 'Angie'),
(416, 13, 308, 'bDpi8EdPMhU', 'JOYRIDE', 'Kesha', 150, 'Angie'),
(417, 13, 309, 'KawV_oK6lIc', 'Grown-up\'s Paradise', 'Mili;Monster Siren Records', 254, 'Angie'),
(418, 13, 310, 'AX3Bsiq-13k', 'Kiss and Make Up', 'Dua Lipa;BLACKPINK', 189, 'Angie'),
(419, 13, 311, '2ByCR2DRids', 'The Only Heartbreaker', 'Mitski', 185, 'Angie'),
(420, 13, 312, 't6hN8nGcpY8', 'Working for the Knife', 'Mitski', 159, 'Angie'),
(421, 13, 313, 'mHKTdlUyyko', 'Francis Forever', 'Mitski', 150, 'Angie'),
(422, 13, 314, 'AGCL3icu9dk', 'Me and My Husband', 'Mitski', 137, 'Angie'),
(423, 13, 315, '0Szr5Dcwn4Y', 'Passion', 'PinkPantheress', 138, 'Angie'),
(424, 13, 316, '3iAXclHlTTg', '脳裏上のクラッカー', 'ZUTOMAYO', 269, 'Angie'),
(425, 13, 317, 'dcOwj-QE_ZE', '暗く黒く', 'ZUTOMAYO', 258, 'Angie'),
(426, 13, 318, 'UnIhRpIT7nc', 'ラグトレイン', 'INABAKUMORI', 252, 'Angie'),
(427, 13, 319, 'DeKLpgzh-qQ', 'ロストアンブレラ', 'INABAKUMORI', 203, 'Angie'),
(428, 13, 320, 'qJhb43oLbDs', 'hand crushed by a mallet', '100 gecs;Laura Les;Dylan Brady', 127, 'Angie'),
(429, 13, 321, 'LvQIZ1IqyFc', 'Remember My Name', 'Mitski', 135, 'Angie'),
(430, 13, 322, '3vjkh-acmTE', 'Washing Machine Heart', 'Mitski', 129, 'Angie'),
(431, 13, 323, 'fdPL_dToT4k', 'Pink in the Night', 'Mitski', 137, 'Angie'),
(432, 13, 324, 'TbsBEb1ZxWA', 'Lone Digger', 'Caravan Palace', 231, 'Angie'),
(433, 13, 325, 'glbmprjG3zw', 'Hai Yorokonde', 'Kocchi no Kento', 161, 'Angie'),
(434, 13, 326, 'kFZKgf5WG0g', 'Absolute Territory', 'Ken Ashcorp', 269, 'Angie'),
(435, 13, 327, 'i2OOruTFi80', 'Califórnica', 'La Gusana Ciega', 210, 'Angie'),
(436, 13, 328, '4A3poKE6qnM', 'Everything at Once', 'Lenka', 158, 'Angie'),
(437, 13, 329, 'G_JfKOjwzwo', 'Through Patches of Violet', 'Mili', 233, 'Angie'),
(438, 13, 330, 'XVsg27hh2S4', 'Hope Is the Thing With Feathers', 'Robin;HOYO-MiX;Chevy', 230, 'Angie'),
(439, 13, 331, 'npyiiInMA0w', 'Sway to My Beat in Cosmos', 'Robin;HOYO-MiX;Chevy', 165, 'Angie'),
(440, 13, 332, '_57ZW9kq1X8', 'Candy Store', 'Jessica Keenan Wynn;Alice Lee;Elle McLemore', 173, 'Angie'),
(441, 13, 333, 'GAOxJv96VE8', 'Freeze Your Brain', 'Ryan McCartan;Barrett Wilbert Weed', 171, 'Angie'),
(442, 13, 334, 'Er6cBpR63XA', 'Seventeen', 'Barrett Wilbert Weed;Ryan McCartan', 192, 'Angie'),
(443, 13, 335, 'qigbBgDapTc', 'Deep In Abyss - Anime Intro Version', 'Miyu Tomita;Mariye Ise', 223, 'Angie'),
(444, 13, 336, 'OgDllcyrGuc', 'ENDLESS EMBRACE', 'MYTH & ROID', 328, 'Angie'),
(445, 13, 337, 'vtJscsqrhL4', 'THE SHAPE OF', '安月名莉子', 213, 'Angie'),
(446, 13, 338, 'uJxaUi9f5vo', 'Duetting Solo', 'Mili', 155, 'Angie'),
(447, 13, 339, '2Tl70c6-m3I', 'A Guy That I\'d Kinda Be Into', 'Stephanie Hsu;\'Be More Chill\' Ensemble;Be More Chill', 165, 'Angie'),
(448, 13, 340, 'r105CzDvoo0', 'Anytime Anywhere', 'milet', 261, 'Angie'),
(449, 13, 341, 'dddHFJ-1JNk', 'La Primera Versión', 'La Oreja de Van Gogh', 264, 'Angie'),
(450, 13, 342, 'Pj6ntDEEfeE', 'The Red Means I Love You', 'Madds Buckley', 238, 'Angie'),
(451, 13, 343, 'cqwcH_WIP-8', 'It\'s Going Down Now', 'Azumi Takahashi;Lotus Juice;ATLUS Sound Team;ATLUS GAME MUSIC', 183, 'Angie'),
(452, 13, 344, 'AWdiMzS1cEk', 'El Ultimo Vals', 'La Oreja de Van Gogh', 205, 'Angie'),
(453, 13, 345, 'Jr-vAwJLUJc', 'Wait a Minute! (Sped Up) - I Think I Left My Consciousness in the 6Th Dimension', 'Hiko', 116, 'Angie'),
(454, 13, 346, 'f-4olQU-YBo', 'Sand Planet', 'Master Andross;JubyPhonic', 266, 'Angie'),
(455, 13, 347, 'DVA83bnFbu4', 'Between Two Worlds', 'Mili', 297, 'Angie'),
(456, 13, 348, '-PgImeQfrEs', 'The Lighthouse', 'Halsey', 273, 'Angie'),
(457, 13, 349, 'zDQ1rWjzgoo', 'Elevator Man', 'Oingo Boingo', 271, 'Angie'),
(458, 13, 350, 'gq1eCtGCowM', 'Identity', 'Sarina', 273, 'Angie'),
(459, 13, 351, 'fLzU21ltH4U', 'No_se_ve.mp3', 'Emilia;LUDMILLA;ZECCA', 206, 'Angie'),
(460, 13, 352, 'ld3p2BbUwO4', 'Pasarela', 'Daddy Yankee', 200, 'Angie'),
(461, 13, 353, 'cU-8jnh57GY', 'La Noche De Los Dos', 'Daddy Yankee;Natalia Jiménez', 224, 'Angie'),
(462, 13, 354, 'LEbsx5sYZ3s', 'Versos Perversos', 'Lil Bokeron', 127, 'Angie'),
(463, 13, 355, '-Qn_yN4g8IM', 'The Vampire', 'DECO*27', 178, 'Angie'),
(464, 13, 356, 'OIBODIPC_8Y', '勇者', 'YOASOBI', 204, 'Angie'),
(465, 13, 357, 'gXfa8PwDP9Y', 'Bugambilia', 'Nasa Histoires', 190, 'Angie'),
(466, 13, 358, '_ypjBAIz0EQ', 'Step On Me', 'The Cardigans', 230, 'Angie'),
(467, 13, 359, 'GLg2352T_vc', 'Lovefool', 'The Cardigans', 197, 'Angie'),
(468, 13, 360, 'UZton86SuOg', 'Material Girl', 'Madonna', 233, 'Angie'),
(469, 13, 361, 'skH-wooEasg', 'GIVE ME RICE', 'Mili', 187, 'Angie'),
(470, 13, 362, '3GcgxkZuDjs', 'Fushicho', 'Mili', 228, 'Angie'),
(471, 13, 363, 'RWX8NRIOf64', 'Opium', 'Mili', 184, 'Angie'),
(472, 13, 364, 'Hy0bdQpEGPI', 'Ikutoshitsuki', 'Mili', 209, 'Angie'),
(473, 13, 365, 'apaJaq9WBUQ', 'DK', 'Mili', 150, 'Angie'),
(474, 13, 366, 'et7QuZPSC_U', 'Bulbel', 'Binary Haze Interactive;Mili', 206, 'Angie'),
(475, 13, 367, 'JnB0BrnZj2w', 'Rosetta', 'Mili', 181, 'Angie'),
(476, 13, 368, '7mIUHWR6vIc', 'Chocological', 'Mili', 261, 'Angie'),
(477, 13, 369, 'eCm--tb5SKg', 'Utopiosphere', 'Mili', 133, 'Angie'),
(478, 13, 370, 'vvc0wA5XYaQ', 'Sacramentum:Unaccompanied Hymn for Torino', 'Mili', 209, 'Angie'),
(479, 13, 371, 'XmaZv4RuzY8', 'A Turtle\'s Heart', 'Mili', 183, 'Angie'),
(480, 13, 372, 'UAsjM8kLvuE', 'Mitsubachi', 'Mili', 179, 'Angie'),
(481, 13, 373, 'AlP5g4C7xG0', 'Static', 'Mili', 214, 'Angie'),
(482, 13, 374, 'IYiHNzFoA8g', 'My Creator', 'Mili', 193, 'Angie'),
(483, 13, 375, 'L8Jtgj8j4tY', 'Skin-Deep Comedy', 'Promise of wizard;Mili', 322, 'Angie'),
(484, 13, 376, 'lkda0SYeZbA', 'Dandelion Girls, Dandelion Boys', 'Mili', 174, 'Angie'),
(485, 13, 377, '7z4WJAEG3u8', 'Rightfully (TV Animation Goblin Slayer opening)', 'Mili', 211, 'Angie'),
(486, 13, 378, '92E0X59wzeg', 'Compass', 'Mili', 169, 'Angie'),
(487, 13, 379, 'Wykhe7OgZeA', 'Paper Bouquet', 'Mili', 209, 'Angie'),
(488, 13, 380, 'DVA83bnFbu4', 'Between Two Worlds - Realm of Darkness', 'Mili', 297, 'Angie'),
(489, 13, 381, 'AlP5g4C7xG0', 'Static', 'Mili', 214, 'Angie'),
(490, 13, 382, 'tLQLa6lM3Us', 'Entertainment -Goblin Slayer II Opening Theme-', 'Mili', 181, 'Angie'),
(491, 13, 383, 'Js2iTjf9lRg', 'Phantomcat of Meowloween', 'Mili', 165, 'Angie'),
(492, 13, 384, 'q0qU9iTOX14', 'Petrolea', 'Mili', 261, 'Angie'),
(493, 13, 385, 'xkaF_Ox6FZc', 'Imagined Flight', 'Mili', 211, 'Angie'),
(494, 13, 386, 'In5Du5x6MZM', 'Iron Lotus', 'Mili', 240, 'Angie'),
(495, 13, 387, 'bLRq4Dxs4HI', 'War of Shame', 'Mili', 175, 'Angie'),
(496, 13, 388, 'q3Vj8fnq51M', 'Cast Me a Spell', 'Promise of wizard;Mili', 140, 'Angie'),
(497, 13, 389, 'jvFRCD9PplE', 'Dancing Ghost\'s Ball Jointed Darling', 'Mili', 194, 'Angie'),
(498, 13, 390, 'XfTWgMgknpY', 'In Hell We Live, Lament - Let\'s Lament', 'Mili;KIHOW', 225, 'Angie'),
(499, 13, 391, '8nYhNlj8XPI', 'Between Two Worlds - Let\'s Lament', 'Mili', 363, 'Angie'),
(500, 13, 392, 'xyx8DMlUAQ4', 'From a Place of Love', 'Mili', 182, 'Angie'),
(501, 13, 393, '7z4WJAEG3u8', 'Rightfully (TV Animation Goblin Slayer opening)', 'Mili', 211, 'Angie'),
(502, 13, 394, 'eSW2LVbPThw', 'ラビットホール', 'DECO*27', 162, 'Angie'),
(503, 13, 395, 'C11FEkBaGg8', 'Above and Beyond', 'Riproducer', 297, 'Angie'),
(504, 13, 396, 'khNi_6PnvaE', 'The Fox\'s Wedding', 'MASA WORKS DESIGN', 248, 'Angie'),
(505, 13, 397, '5NarVgDFNX0', 'Idol', 'YOASOBI', 226, 'Angie'),
(506, 13, 398, 'C-7TIDIKc98', 'ボルテッカー', 'DECO*27', 153, 'Angie'),
(507, 13, 399, 'MgNCjYXCxOc', 'Whisper Whisper Whisper', 'Azari', 125, 'Angie'),
(508, 13, 400, 'qFow8LkHtlU', 'THE GREATEST LIVING SHOW', 'Itoki Hana;Toby Fox', 296, 'Angie'),
(509, 13, 401, 'EDvS-lPPBGs', 'Chemtrails Over The Country Club', 'Lana Del Rey', 271, 'Angie'),
(510, 13, 402, 'xtfXl7TZTac', 'Into The Night', 'YOASOBI', 262, 'Angie'),
(511, 13, 403, 'Iu3ntUb2YwI', 'Alien Alien', 'Nayutalien', 184, 'Angie'),
(512, 13, 404, 'ocAKhyWuawo', 'わたしのアール', 'WADATAKEAKI KurageP', 214, 'Angie'),
(513, 13, 405, '-047ko4v05s', 'Washing Machine Heart', 'Mitski', 128, 'Angie'),
(514, 13, 406, '11Qj2Tpkh0U', 'Nobody', 'Mitski', 193, 'Angie'),
(515, 13, 407, 'CwGbMYLjIpQ', 'My Love Mine All Mine', 'Mitski', 138, 'Angie'),
(516, 13, 408, 'XfMBdq5iFnw', 'I Bet on Losing Dogs', 'Mitski', 171, 'Angie'),
(517, 13, 409, 'LvQIZ1IqyFc', 'Remember My Name', 'Mitski', 135, 'Angie'),
(518, 13, 410, 'fdPL_dToT4k', 'Pink in the Night', 'Mitski', 137, 'Angie'),
(519, 13, 411, '-KttTf9jyT8', 'Jobless Monday', 'Mitski', 127, 'Angie'),
(520, 13, 412, 'WNvH7sUJM8U', 'Getting Even', 'SURAI', 293, 'Angie'),
(521, 13, 413, 'LCo0KdbVIJM', 'Burning Desires', 'Sān-Z;HOYO-MiX', 139, 'Angie'),
(522, 13, 414, 'b41yyHH6Cic', 'Flowerworks', 'Promise of wizard;Mili', 226, 'Angie'),
(523, 13, 415, '_v_Voe5KD1M', 'Re:Re:', 'ASIAN KUNG-FU GENERATION', 229, 'Angie'),
(524, 13, 416, '48Csilkjev0', 'ネバーランド', 'DECO*27', 157, 'Angie'),
(525, 13, 417, 'ZuifkacZ0TA', 'Hero', 'Mili', 215, 'Angie'),
(526, 13, 418, 'nbeW-itQxR0', 'Path to Isolation', 'Jeff Williams;Casey Lee Williams', 240, 'Angie'),
(527, 13, 419, 'EgL4XbhkCSk', 'KYABA HO', 'PiNKII', 151, 'Angie'),
(528, 13, 420, '8Bu3N-2tA_0', 'Impacto', 'Enjambre', 238, 'Angie'),
(529, 13, 421, 'kbNdx0yqbZE', 'モニタリング', 'DECO*27', 181, 'Angie'),
(530, 13, 422, 'WvKd91KwTKM', 'Life We Sow', '魔法使いの約束', 209, 'Angie'),
(531, 13, 423, '8Ebqe2Dbzls', 'APT.', 'ROSÉ;Bruno Mars', 170, 'Angie'),
(532, 13, 424, 'xCh1T6fcRo8', 'Good Luck, Babe!', 'Chappell Roan', 218, 'Angie'),
(533, 13, 425, 'y9Wxl9Q9lUQ', 'Red Wine Supernova', 'Chappell Roan', 193, 'Angie'),
(534, 13, 426, 'ukBmTEOzueY', 'HOT TO GO!', 'Chappell Roan', 190, 'Angie'),
(535, 13, 427, 'n7ZmBBf5-7g', '自傷無色 (feat. 宵崎奏&朝比奈まふゆ&初音ミク)', '25時、ナイトコードで。', 218, 'Angie'),
(536, 13, 428, 'eWBjxT54RQA', 'ビターチョコデコレーション (feat. 宵崎奏&朝比奈まふゆ&東雲絵名&暁山瑞希&初音ミク)', '25時、ナイトコードで。', 199, 'Angie'),
(537, 13, 429, 'vp6XdbG3AhA', 'Pink Pony Club', 'Chappell Roan', 259, 'Angie'),
(538, 13, 430, '7hVIf9YD6Vk', 'My Kink Is Karma', 'Chappell Roan', 223, 'Angie'),
(539, 13, 431, 'k0optPS9qrA', 'A Night To Remember', 'beabadoobee;Laufey', 233, 'Angie'),
(540, 13, 432, 'dExLwcq2-0g', 'Should\'ve Been Me', 'Mitski', 192, 'Angie'),
(541, 13, 433, 'L9uj2XChyBI', 'Headlock', 'Imogen Heap', 216, 'Angie'),
(542, 13, 434, 'kq2BCYAKDNc', 'From the Start', 'Good Kid', 150, 'Angie'),
(543, 13, 435, 'Xzy8KwjXbpY', 'Mimukauwa Nice Try', 'nunununununununununununununununununununununununununununununununununununununununununununununununununununununununununununununununununununununu', 38, 'Angie'),
(544, 13, 436, 'NA3MJmcyPpE', 'Water the Roses', 'FLAVOR FOLEY', 269, 'Angie'),
(545, 13, 437, 'KUYK5KEU6qg', 'Real Man', 'beabadoobee', 161, 'Angie'),
(546, 13, 438, '_4BBA8y3DPc', 'Casual', 'Chappell Roan', 234, 'Angie'),
(547, 13, 439, 'sV2H712ldOI', 'Confessions of a Rotten Girl', 'SAWTOWNE', 208, 'Angie'),
(548, 13, 440, '9t-uBxQzyXQ', 'Year N', 'Mili', 207, 'Angie'),
(549, 13, 441, '51GIxXFKbzk', 'INTERNET YAMERO', 'NEEDY GIRL OVERDOSE;KOTOKO;Aiobahn +81', 244, 'Angie'),
(550, 13, 442, '3omB6DNSTbI', 'Original Me', 'Sān-Z;HOYO-MiX', 223, 'Angie'),
(551, 13, 443, '8oBV3jPTW4s', 'ロストワンの号哭', 'Neru', 218, 'Angie'),
(552, 13, 444, 'slVPS_VJqhs', 'KARMANATIONS', 'Akatsuki Records', 229, 'Angie'),
(553, 13, 445, 'bjYxllq-Uuc', 'WHAT ROBOTS NEED - hazama RUNWAY ver.', 'AWAAWA', 249, 'Angie'),
(554, 13, 446, 'xdaKBAuO8zg', 'Femininomenon', 'Chappell Roan', 220, 'Angie'),
(555, 13, 447, '-KE8NmtTlPk', 'After Midnight', 'Chappell Roan', 205, 'Angie'),
(556, 13, 448, 'olxdCY7hHEw', 'Coffee', 'Chappell Roan', 206, 'Angie'),
(557, 13, 449, 'kWLai0XoZmg', 'Super Graphic Ultra Modern Girl', 'Chappell Roan', 188, 'Angie'),
(558, 13, 450, 'EqUHnojFH5Y', 'Picture You', 'Chappell Roan', 187, 'Angie'),
(559, 13, 451, 'E9sngJmXijQ', 'Kaleidoscope', 'Chappell Roan', 223, 'Angie'),
(560, 13, 452, 'QW2Alij7jlY', 'Naked In Manhattan', 'Chappell Roan', 211, 'Angie'),
(561, 13, 453, 'GcXlN7meclE', 'California', 'Chappell Roan', 211, 'Angie'),
(562, 13, 454, 'xjBKcdFU3Hs', 'Guilty Pleasure', 'Chappell Roan', 225, 'Angie'),
(563, 13, 455, 'WD49SrC8mEU', 'Process', '魔法使いの約束;Mili', 205, 'Angie'),
(564, 13, 456, 'DtKXThAkQnk', 'Wicked', 'Crusher-P;Eleanor Forte', 230, 'Angie'),
(565, 13, 457, 'wtde2lGAAd8', 'The Brave', 'YOASOBI', 194, 'Angie'),
(566, 13, 458, 'QgxYScXawEE', 'POP IN 2', 'B小町;ルビー(CV:伊駒ゆりえ);有馬かな(CV:潘めぐみ);MEMちょ(CV:大久保瑠美)', 267, 'Angie'),
(567, 13, 459, 'mcG0nYC89tQ', 'miragecoordinator', 'zts', 433, 'Angie'),
(568, 13, 460, 'bIBoVGwBVRM', 'worldenddominator', 'zts', 459, 'Angie'),
(569, 13, 461, 'HUH4YnLTBuQ', 'happiness of marionette', 'Dai', 140, 'Angie'),
(570, 13, 462, 'fcnDmrtj6Sk', 'Endless Nine', 'Dai', 240, 'Angie'),
(571, 13, 463, '3uJR22WksQs', 'Girl Inside', 'Mika Kobayashi', 190, 'Angie'),
(572, 13, 464, 'EzxsTXNmVm0', 'This Is A Life', 'Son Lux;Mitski;David Byrne', 161, 'Angie'),
(573, 13, 465, 'obxFWNeGDOg', 'Man\'s World', 'MARINA', 213, 'Angie'),
(574, 13, 466, '9gO0ooqA8Vg', 'Purge The Poison', 'MARINA', 195, 'Angie'),
(575, 13, 467, 'juyXTc0lxfY', 'Highly Emotional People', 'MARINA', 214, 'Angie'),
(576, 13, 468, 'HD00JavclfQ', 'New America', 'MARINA', 233, 'Angie'),
(577, 13, 469, 'uTegeMyRn0U', 'Pandora\'s Box', 'MARINA', 212, 'Angie'),
(578, 13, 470, 'wT2uhkNGLUk', 'I Love You But I Love Me More', 'MARINA', 224, 'Angie'),
(579, 13, 471, 'B5-qITdOuSo', 'Flowers', 'MARINA', 234, 'Angie'),
(580, 13, 472, 'b3JZY7cEwlc', 'Goodbye', 'MARINA', 283, 'Angie'),
(581, 13, 473, 'iKbV_PZccts', 'Say What?', 'B小町;ルビー(CV:伊駒ゆりえ);有馬かな(CV:潘めぐみ);MEMちょ(CV:大久保瑠美)', 212, 'Angie'),
(582, 13, 474, 'VL-n2KNHe7M', '深海52Hz', 'B小町;ルビー(CV:伊駒ゆりえ);有馬かな(CV:潘めぐみ);MEMちょ(CV:大久保瑠美)', 175, 'Angie'),
(583, 13, 475, 'c56TpxfO9q0', 'テレパシ', 'DECO*27', 145, 'Angie'),
(584, 13, 476, 'l0gaedmUogA', 'Classroom Dreamer', 'Mili', 450, 'Angie'),
(585, 13, 477, 'm_bIZL5C1aw', 'Nectar (feat. Cassie Wei)', 'Mili;Yamato Kasai;Cassie Wei', 163, 'Angie'),
(586, 13, 478, '3k8elXd-W_M', 'V&D', 'Mili;Yamato Kasai', 121, 'Angie'),
(587, 13, 479, '37Npawv4Ais', 'Taboo', 'Mili;Yamato Kasai', 112, 'Angie'),
(588, 13, 480, 'miJAVDm1NvY', 'White', 'Mili;Yamato Kasai', 86, 'Angie'),
(589, 13, 481, 'YrO_c2D5Hxw', 'A secret', 'Mili;Yamato Kasai', 75, 'Angie'),
(590, 13, 482, '-Oo138Q6d4Y', 'Root', 'Mili;Yamato Kasai', 175, 'Angie'),
(591, 13, 483, 'sFjy8tjLric', 'See you later', 'Mili;Yamato Kasai', 49, 'Angie'),
(592, 13, 484, 'nsJdXqR1wMY', 'Confidentiality', 'Mili;Yamato Kasai', 75, 'Angie'),
(593, 13, 485, 'CeHEl5tyLbw', 'Empty dignity', 'Mili;Yamato Kasai', 163, 'Angie'),
(594, 13, 486, '-5Y0r4HoMKg', 'Magnolia denudata', 'Mili;Yamato Kasai', 197, 'Angie'),
(595, 13, 487, 'jIoCKvW2JTU', 'Gordita (feat. Residente Calle 13)', 'Shakira;Calle 13', 205, 'Angie'),
(596, 13, 488, 'N91f4lQM86E', 'Biri-Biri', 'YOASOBI', 188, 'Angie'),
(597, 13, 489, 'emVNCcwCtuc', 'I Am a Fluff', 'Mili', 267, 'Angie'),
(598, 13, 490, 'IC8-oN8uCXo', 'Kaiju', 'sakanaction', 253, 'Angie'),
(599, 13, 491, 'fhTFysCtF6g', 'アポリア', 'Yorushika', 242, 'Angie'),
(600, 13, 492, 'k5mX3NkA7jM', 'Mary On A Cross', 'Ghost', 245, 'Angie'),
(601, 13, 493, 'd0BLq72ggeg', 'wi(l)d-screen baroque', 'Daiba Nana (CV: Moeka Koizumi)', 261, 'Angie'),
(602, 13, 494, 'dh57g0GMQWU', 'Classroom Dreamer', 'Mili', 206, 'Angie'),
(603, 13, 495, 'OWT6Ixe3c94', 'Hearts Stay Unchanged', 'Mili', 273, 'Angie'),
(604, 13, 496, 'Groovai-RT4', 'ダイダイダイダイダイキライ', 'Amala', 18, 'Angie'),
(605, 13, 497, 'xUjpCR3qiz0', 'Porque te vas', 'Jeanette', 205, 'Angie'),
(606, 13, 498, 'LIgC1EwZczk', 'Wrap Me In Plastic - Marcus Layton Radio Edit', 'CHROMANCE;Marcus Layton', 194, 'Angie'),
(607, 13, 499, '6JgG8fZAlu0', 'Girls In Bikinis', 'Poppy', 145, 'Angie'),
(608, 13, 500, 'HcYN5Gn5IuM', 'コネクト', 'ClariS', 272, 'Angie'),
(609, 13, 501, '_sOKkON_UnQ', '4:00A.M.', 'Taeko Onuki', 337, 'Angie'),
(610, 13, 502, 'hNF4_Xy7pNM', 'My Alcoholic Friends', 'The Dresden Dolls', 168, 'Angie'),
(611, 13, 503, 'OTIgSuOI-i8', 'Running up that Hill (Nightcore)', 'Syrex', 113, 'Angie'),
(612, 13, 504, 'GtEKUILjguA', 'Black Rock Shooter', 'mulmeyun', 302, 'Angie');
INSERT INTO `playlist_tracks` (`id`, `playlist_id`, `position`, `video_id`, `title`, `artist`, `duration`, `added_by`) VALUES
(613, 13, 505, 'AbRVk7lIhac', '細菌汚染 - Bacterial Contamination -', 'mathru(KanimisoP)', 237, 'Angie'),
(614, 13, 506, 'eVY7i-DT0rs', 'チュチュ・バレリーナ', 'もな・るか・みき from AIKATSU☆STARS!', 91, 'Angie'),
(615, 13, 507, '27YVhrTATWo', '裸足のルネサンス', 'りえ・ななせ from AIKATSU☆STARS!', 262, 'Angie'),
(616, 13, 508, 'yEUNBao61kY', 'チュチュ・バレリーナ - ユリカ & かえで ver.', 'れみ;ゆな', 282, 'Angie'),
(617, 13, 509, '27YVhrTATWo', '裸足のルネサンス - 〜レイ ver.〜', 'りえ from AIKATSU☆STARS!', 262, 'Angie'),
(618, 13, 510, 'icoRdEwYzAQ', 'MEN I LOVE', 'AWAAWA', 236, 'Angie'),
(619, 13, 511, 'cqao7blU4u0', 'Believe it - あいね & エマ ver.', 'Aine;Ema', 268, 'Angie'),
(620, 13, 512, 'szyPY8nbBF4', 'TIAN TIAN', 'Mili', 254, 'Angie'),
(621, 13, 513, 'KlTNKOnfXFk', 'Static', 'FLAVOR FOLEY', 244, 'Angie'),
(622, 13, 514, 'vD_D3zQ4Ais', 'The only sun light', 'りさ', 281, 'Angie'),
(623, 13, 515, '3iUgKH8c7p4', 'Retry Now', 'NAKISO', 123, 'Angie'),
(624, 13, 516, 'LvYL8u4p-aM', 'Spoken For', 'FLAVOR FOLEY', 254, 'Angie'),
(625, 13, 517, '8E8aWeY-pAc', 'Still Waiting for Your Reply? - Ojisan Style Text (feat. Ui)', 'yoshimoto ojisan;Ui', 188, 'Angie'),
(626, 13, 518, 'BI9Ue6JwJic', 'チェリーポップ', 'DECO*27', 142, 'Angie'),
(627, 13, 519, 'eTplxWaAD8o', '天天天国地獄国', 'Aiobahn +81;Nanahira;P丸様｡', 234, 'Angie'),
(628, 13, 520, 'sDSJ5E8uzZU', 'Water Fountain', 'Alec Benjamin', 219, 'Angie'),
(629, 13, 521, 'mzmjdntlRJk', 'Paper Crown', 'Alec Benjamin', 200, 'Angie'),
(630, 13, 522, '8HWFJjRx2y4', 'Water Fountain - Nightcore', 'NightcoreMuzzic;TommyMuzzic', 203, 'Angie'),
(631, 13, 523, 'dmx_aVPGhs8', 'Pen : Chikara : Katana', 'Hoshimi Junna (CV: Hinata Sato);Daiba Nana (CV: Moeka Koizumi)', 597, 'Angie'),
(632, 13, 524, 'cA64x2PQTsU', 'I Deserve to Bleed', 'Sushi Soucy', 104, 'Angie'),
(633, 13, 525, 'YTvIG6gHMh0', 'All The Things She Said', 'Poppy', 224, 'Angie'),
(634, 13, 526, 'nOxH6KEh5n4', 'Digital Silence', 'Peter McPoland', 211, 'Angie'),
(635, 13, 527, '8yIHZCD6FEs', 'Bedroom Hymns', 'Florence + The Machine', 183, 'Angie'),
(636, 13, 528, '6C33PKdV710', 'Teeth', '5 Seconds of Summer', 204, 'Angie'),
(637, 13, 529, 'W8BYJzn0F0U', 'LA DI DA', 'EVERGLOW', 211, 'Angie'),
(638, 13, 530, 'Qgduhk26sIw', 'Stunnin\'', 'Curtis Waters;Harm Franklin', 149, 'Angie'),
(639, 13, 531, 'RkID8_gnTxw', 'THE BADDEST', 'K/DA;i-dle;Wolftyla;Bea Miller;League of Legends', 175, 'Angie'),
(640, 13, 532, 'jhwoPACmsd8', 'Let\'s Kill Tonight', 'Panic! At The Disco', 214, 'Angie'),
(641, 13, 533, '76ZPTnduToo', 'My Songs Know What You Did In The Dark (Light Em Up)', 'Fall Out Boy', 186, 'Angie'),
(642, 13, 534, 'HyHNuVaZJ-k', 'Feel Good Inc.', 'Gorillaz;De La Soul', 255, 'Angie'),
(643, 13, 535, 'WuzIw73pmSc', 'My Ordinary Life', 'The Living Tombstone', 231, 'Angie'),
(644, 13, 536, 'XSQUZv8ipCE', 'Oh No!', 'MARINA', 180, 'Angie'),
(645, 13, 537, 'B1u2Cg0Zlt4', 'Victorious', 'Panic! At The Disco', 180, 'Angie'),
(646, 13, 538, 'mHKTdlUyyko', 'Francis Forever', 'Mitski', 150, 'Angie'),
(647, 13, 539, '-KttTf9jyT8', 'Jobless Monday', 'Mitski', 127, 'Angie'),
(648, 13, 540, '9XRIj1_OTxA', 'Old Friend', 'Mitski', 113, 'Angie'),
(649, 13, 541, 'BjGB9hc5huk', 'Your Best American Girl', 'Mitski', 212, 'Angie'),
(650, 13, 542, 'K0-0AL4Wh00', 'Nobody', 'Mitski', 192, 'Angie'),
(651, 13, 543, 'mje7DWeqFIs', 'Why Didn\'t You Stop Me?', 'Mitski', 142, 'Angie'),
(652, 13, 544, 'tQIqGGb0JUc', 'Stay Soft', 'Mitski', 195, 'Angie'),
(653, 13, 545, 'sbLLdy5cdmY', 'Love Me More', 'Mitski', 213, 'Angie'),
(654, 13, 546, 'KUfkfJfsKrc', 'Two Slow Dancers', 'Mitski', 239, 'Angie'),
(655, 13, 547, 'A2LEaF1jCeA', 'I Will', 'Mitski', 175, 'Angie'),
(656, 13, 548, 'S09qwKoSHbM', 'KICK BACK', 'Kenshi Yonezu', 194, 'Angie'),
(657, 13, 549, 'BGztdO-GWsw', 'The Moon Will Sing', 'The Crane Wives', 0, 'Angie'),
(658, 13, 550, '1oMk2tK3YGs', 'The Family Jewels', 'MARINA', 246, 'Angie'),
(659, 13, 551, 'YCKfg6kHKTg', 'End It', 'RIELL', 196, 'Angie'),
(660, 13, 552, 'ja_Tuacypbc', 'I Didn\'t Ask For This', 'Beth Crowley', 214, 'Angie'),
(661, 13, 553, '3VTkBuxU4yk', 'MORE', 'K/DA;Madison Beer;i-dle;Lexie Liu;Jaira Burns;Seraphine;League of Legends', 231, 'Angie'),
(662, 13, 554, 'jU77vmVuYys', 'U (From Belle Soundtrack) - English Version', 'ꉈꀧ꒒꒒ꁄꍈꍈꀧ꒦ꉈ ꉣꅔꎡꅔꁕꁄ;Belle', 288, 'Angie'),
(663, 13, 555, 'hgyLUeP5UxI', 'LALISA', 'LISA', 203, 'Angie'),
(664, 13, 556, 'VJy8qZ77bpE', '眩しいDNAだけ', 'ZUTOMAYO', 237, 'Angie'),
(665, 13, 557, 'x6yGHOpIe5c', 'Burning Pile', 'Mother Mother', 0, 'Angie'),
(666, 13, 558, 'phDQEkp2FTw', 'Oh my god', 'i-dle', 195, 'Angie'),
(667, 13, 559, 'KushW6zvazM', 'Ghost Rule', 'DECO*27', 206, 'Angie'),
(668, 13, 560, 'AA7l0qPQ8HY', 'Nxde', 'i-dle', 178, 'Angie'),
(669, 13, 561, 'gh8Yz7XbaRk', 'Hermit the Frog', 'MARINA', 0, 'Angie'),
(670, 13, 562, 'YTvIG6gHMh0', 'All The Things She Said', 'Poppy', 0, 'Angie'),
(671, 13, 563, 'UOxkGD8qRB4', 'POP/STARS', 'K/DA;Madison Beer;i-dle;Jaira Burns;League of Legends', 203, 'Angie'),
(672, 13, 564, 'mcU1VCgcUh8', 'Dead Girl Walking', 'Barrett Wilbert Weed;Ryan McCartan', 205, 'Angie'),
(673, 13, 565, 'LSPXSShg8vs', 'Therefor you and me', 'si-o', 0, 'Angie'),
(674, 13, 566, 'Dao5P8Mqkzw', 'Wrecking Ball', 'Mother Mother', 194, 'Angie'),
(675, 13, 567, 'F57P9C4SAW4', 'California Gurls', 'Katy Perry;Snoop Dogg', 234, 'Angie'),
(676, 13, 568, '_kIrRooQwuk', 'Big God', 'Florence + The Machine', 269, 'Angie'),
(677, 13, 569, 'iFaZ865eNZo', 'Are You Satisfied?', 'MARINA', 198, 'Angie'),
(678, 13, 570, 'KUfkfJfsKrc', 'Two Slow Dancers', 'Mitski', 239, 'Angie'),
(679, 13, 571, '7LhZOJM7p7k', 'Quiet', 'Lights', 194, 'Angie'),
(680, 13, 572, 'SNU442anX-8', 'I Wouldn\'t Mind', 'He Is We', 199, 'Angie'),
(681, 13, 573, 'YCxxWli-gkA', 'Satisfied', 'Renée Elise Goldsberry;Original Broadway Cast of Hamilton', 330, 'Angie'),
(682, 13, 574, 'hC22VEFRM4Q', 'ドラマツルギー', 'Rib', 246, 'Angie'),
(683, 13, 575, 'pkSStiPbcsA', 'Don', 'Miranda!', 183, 'Angie'),
(684, 13, 576, 'o-zNYMO6SyQ', 'Warrior', 'Beth Crowley', 307, 'Angie'),
(685, 13, 577, 'c1sOsg3vtA8', 'INFERNO', 'Sub Urban;Bella Poarch', 133, 'Angie'),
(686, 13, 578, 'tBYU9W1ezL0', 'Camelia', 'Mili', 282, 'Angie'),
(687, 13, 579, '_-9YVWH6YZI', 'Lemonade', 'Mili', 194, 'Angie'),
(688, 13, 580, 'VLE6Y1q13qE', 'DRUM GO DUM', 'K/DA;Wolftyla;Bekuh Boom;Aluna;League of Legends', 201, 'Angie'),
(689, 13, 581, 'DUT5rEU6pqM', 'Hips Don\'t Lie (feat. Wyclef Jean)', 'Shakira;Wyclef Jean', 0, 'Angie'),
(690, 13, 582, 'yvsR-xciOTg', 'Camel By Camel', 'Sandy Marton', 356, 'Angie'),
(691, 13, 583, 'QMGGaNynpLI', 'Unholy (feat. Kim Petras)', 'Sam Smith;Kim Petras', 157, 'Angie'),
(692, 13, 584, 'B5WIqs41rgg', 'Just Dance', 'Lady Gaga;Colby O\'Donis', 0, 'Angie'),
(693, 13, 585, 'qDP_HuBaQfs', 'Work', 'Rihanna;Drake', 219, 'Angie'),
(694, 13, 586, 'eHx--ZtG_Ds', 'Call Me Maybe', 'Carly Rae Jepsen', 195, 'Angie'),
(695, 13, 587, 'NJDLtltc_N8', 'SloMo', 'Chanel', 180, 'Angie'),
(696, 13, 588, 'cs6XHDDGfsQ', 'Rosas', 'La Oreja de Van Gogh', 240, 'Angie'),
(697, 13, 589, 'x6XiOVqWwIY', 'La Niña Que Llora en Tus Fiestas', 'La Oreja de Van Gogh', 166, 'Angie'),
(698, 13, 590, 'lV6ppJbzQQM', 'Dale Zelda Dale', 'Ganon Rosario', 257, 'Angie'),
(699, 13, 591, 'hEo6FcrhVVc', 'Dulce Locura', 'La Oreja de Van Gogh', 232, 'Angie'),
(700, 13, 592, 'HSUX_TSCIjw', 'Vestido Azul', 'La Oreja de Van Gogh', 193, 'Angie'),
(701, 13, 593, 'xemE6B2K5Rg', 'Magnet', 'Lollia;Chi-chi', 254, 'Angie'),
(702, 13, 594, 'zkgsWurEfoo', 'That\'s Our Lamp', 'Mitski', 145, 'Angie'),
(703, 13, 595, 'JDRyqUx1X8M', 'Shut Down', 'BLACKPINK', 176, 'Angie'),
(704, 13, 596, 'wECwsE4yNSQ', 'Por la Raja de Tu Falda', 'Estopa', 203, 'Angie'),
(705, 13, 597, 'GtTnyEwtW0c', 'Traición', 'Miranda!', 183, 'Angie'),
(706, 13, 598, '4h0BSep-4xs', 'Tu jardín con enanitos', 'Melendi', 240, 'Angie'),
(707, 13, 599, 'WqWYhWQ1gKY', 'Mimimi', 'SEREBRO', 0, 'Angie'),
(708, 13, 600, 'NVI1NohUuAI', '私がモテないのはどう考えてもお前らが悪い', 'Konomi Suzuki;Kiba Of Akiba', 0, 'Angie'),
(709, 13, 601, '9j7mDS5vn9Y', 'RITUAL', 'NECRONOMIDOL', 0, 'Angie'),
(710, 13, 602, '_nO-f2e87Co', 'Yava!', 'BABYMETAL', 0, 'Angie'),
(711, 13, 603, '_T3S3EwFDNU', 'KARATE', 'BABYMETAL', 0, 'Angie'),
(712, 13, 604, 'qOtwfyMa5mM', 'Hands Up x Ayesha - Remix', 'skyemane', 0, 'Angie'),
(713, 13, 605, 'XPOIe6WdhPQ', 'VORACITY', 'MYTH & ROID', 0, 'Angie'),
(714, 13, 606, 'hF7QDvrBT9Q', 'D.D.D.D.', '(K)NoW_NAME', 0, 'Angie'),
(715, 13, 607, 'y6pgyNmXtQw', 'Mr. Delincuente', 'Norykko', 0, 'Angie'),
(716, 13, 608, 'MDErQ1KTzaI', 'PARANOIA', 'HEARTSTEEL;League of Legends;BAEKHYUN;tobi lou;ØZI;Cal Scruby', 0, 'Angie'),
(717, 13, 609, 'sVZpHFXcFJw', 'GIANTS', 'True Damage;Becky G;Keke Palmer;SOYEON;Duckwrth;Thutmose;League of Legends', 0, 'Angie'),
(718, 13, 610, 'qIobay7xoYM', 'Suerte (Whenever, Wherever)', 'Shakira', 0, 'Angie'),
(719, 13, 611, 'S0GgBcNKK5g', 'Mass Destruction', 'Lotus Juice', 0, 'Angie'),
(720, 13, 612, 'zyhml1UG6ZY', 'FVN!', 'LVL1', 0, 'Angie'),
(721, 13, 613, 'ReFpNaH1_TE', 'Hijo de la Luna', 'Mecano', 0, 'Angie'),
(722, 13, 614, 'bUU4eDJAs-Q', 'i like the way you kiss me', 'Artemas', 0, 'Angie'),
(723, 13, 615, 'xFHNWJVsjmY', 'If I Can Stop One Heart From Breaking', 'Robin;HOYO-MiX;Chevy', 0, 'Angie'),
(724, 13, 616, 'kWS8Y7uqWn4', 'Emergency Budots Dance', 'bimmehh', 0, 'Angie'),
(725, 13, 617, '-q80QowuJSk', 'Monodrama', 'HOYO-MiX', 203, 'Angie'),
(726, 13, 618, 'V2AfXNJImSo', 'Addict', 'PiNKII;Daegho', 0, 'Angie'),
(727, 13, 619, 'jJzw1h5CR-I', 'Dramaturgy', 'Eve', 0, 'Angie'),
(728, 13, 620, '1V_xRb0x9aw', 'Clint Eastwood', 'Gorillaz;Del The Funky Homosapien', 0, 'Angie'),
(729, 13, 621, 'hbckxFs-obM', '薄ら氷心中', 'Sheena Ringo', 0, 'Angie'),
(730, 13, 622, 'Atvsg_zogxo', 'お勉強しといてよ', 'ZUTOMAYO', 0, 'Angie'),
(731, 13, 623, 'uT_Uf5uS27w', 'ma chérie ~愛しい君へ~', 'Malace Mizer', 0, 'Angie'),
(732, 13, 624, '_QCzM4Eei9g', 'メズマライザー (feat. 初音ミク&重音テト)', '32ki;Hatsune Miku;重音テト', 0, 'Angie'),
(733, 13, 625, '0vfZjdK8Ktw', 'The Mind Electric', 'Miracle Musical', 0, 'Angie'),
(734, 13, 626, 'CgxOQjYBmss', 'Dadadadadaru', 'amala', 0, 'Angie'),
(735, 13, 627, 'EBjDpuOF9WE', 'Bang Bang Bang Bang', 'Sohodolls', 0, 'Angie'),
(736, 13, 628, '13EPS_FkMoI', 'Spit It Out', 'BBpanzu', 0, 'Angie'),
(737, 13, 629, '0iVlSNpq8i8', 'BIRDBRAIN', 'Jamie Paige;OK Glass', 0, 'Angie'),
(738, 13, 630, 'bIiIrXM7BUA', 'Bang Bang Bang', 'BBpanzu', 0, 'Angie'),
(739, 13, 631, 'sTyrH4SOwBY', '1000x1000', 'Mili', 0, 'Angie'),
(740, 13, 632, 'jwVMgGs50vE', 'Dracula', 'Tame Impala', 0, 'Angie'),
(741, 13, 633, 'tOzOD-82mW0', 'It\'s Not Like I Like You!!', 'Static-P', 0, 'Angie'),
(742, 13, 634, 'xIF0Me8j0dg', 'Bubble Pop Electric - Remastered 2019', 'Gwen Stefani;Johnny Vulture', 0, 'Angie'),
(743, 13, 635, 'x0jrUwxUFLU', 'Seven Minutes in Heaven', 'Mindless Self Indulgence', 0, 'Angie'),
(744, 13, 636, 'NoyJgR_09Vw', 'Shut Me Up', 'Mindless Self Indulgence', 0, 'Angie'),
(745, 13, 637, '8So3SA2uJvo', 'A Human\'s Touch', 'TWRP;McKenna Rae', 0, 'Angie'),
(746, 13, 638, 'X2Rkmj7Eqtc', 'Bait & Switch', 'KMFDM', 0, 'Angie'),
(747, 13, 639, 'w63orOykrsU', '6up 5oh Cop-Out (Pro / Con)', 'Will Wood and the Tapeworms', 0, 'Angie'),
(748, 13, 640, '5PLN9xzmUSU', 'Skeleton Appreciation Day in Vestal, NY (Bones)', 'Will Wood and the Tapeworms', 0, 'Angie'),
(749, 13, 641, '1esJFm4X8IQ', 'Front Street', 'Will Wood and the Tapeworms', 0, 'Angie'),
(750, 13, 642, 'WTBcm2ZhjuY', '¡Aikido! (Neurotic / Erotic)', 'Will Wood and the Tapeworms', 0, 'Angie'),
(751, 13, 643, 'pWZkJGsocdM', 'White Knuckle Jerk (Where Do You Get Off?)', 'Will Wood and the Tapeworms', 0, 'Angie'),
(752, 13, 644, 'CMgkgZRy9N8', 'Cover This Song (A Little Bit Mine)', 'Will Wood and the Tapeworms', 0, 'Angie'),
(753, 13, 645, 'PEUJBNDJbq4', 'Thermodynamic Lawyer Esq, G.F.D.', 'Will Wood and the Tapeworms', 0, 'Angie'),
(754, 13, 646, 'iOAj4pXWUX0', 'Red Moon', 'Will Wood and the Tapeworms', 0, 'Angie'),
(755, 13, 647, 'KndD5SQxFy4', 'Lysergide Daydream', 'Will Wood and the Tapeworms', 0, 'Angie'),
(756, 13, 648, 'anQpiHHij-k', 'The First Step', 'Will Wood and the Tapeworms', 0, 'Angie'),
(757, 13, 649, '2C7joP3MikE', 'Jimmy Mushrooms\' Last Drink: Bedtime in Wayne, NJ', 'Will Wood and the Tapeworms', 0, 'Angie'),
(758, 13, 650, 'Zl6d35_1FXY', 'Chemical Overreaction / Compound Fracture', 'Will Wood and the Tapeworms', 0, 'Angie'),
(759, 13, 651, 'w63orOykrsU', 'Everything is a Lot', 'Will Wood and the Tapeworms', 0, 'Angie'),
(760, 13, 652, 'pXWGzusJsOo', 'Self-', 'Will Wood and the Tapeworms', 0, 'Angie'),
(761, 13, 653, 'B9wePPxOkLI', '2012', 'Will Wood and the Tapeworms', 0, 'Angie'),
(762, 13, 654, 'Qt5DzjzyEJo', 'Cotard\'s Solution (Anatta, Dukkha, Anicca)', 'Will Wood and the Tapeworms', 0, 'Angie'),
(763, 13, 655, '3Dd3dl7W8rM', 'Mr. Capgras Encounters a Secondhand Vanity: Tulpamancer\'s Prosopagnosia / Pareidolia (As Direct Result of Trauma to Fusiform Gyrus)', 'Will Wood and the Tapeworms', 0, 'Angie'),
(764, 13, 656, 'lst1NGKHQHk', 'The Song with Five Names a.k.a. Soapbox Tao a.k.a. Checkmate Atheists! a.k.a. Neospace Government (A.K.A. You Can Never Know)', 'Will Wood and the Tapeworms', 0, 'Angie'),
(765, 13, 657, '-AvYfjmM7ww', 'Hand Me My Shovel, I\'m Going In!', 'Will Wood and the Tapeworms', 0, 'Angie'),
(766, 13, 658, 'isZbEoAzvLg', 'Dr. Sunshine Is Dead', 'Will Wood and the Tapeworms', 0, 'Angie'),
(767, 13, 659, 'w63orOykrsU', '-ish', 'Will Wood and the Tapeworms', 0, 'Angie'),
(768, 13, 660, 'ui2kW-OvtkA', 'Suburbia Overture / Greetings from Mary Bell Township! / (Vampire) Culture / Love Me, Normally', 'Will Wood', 0, 'Angie'),
(769, 13, 661, 'baquvJnokHY', '2econd 2ight 2eer (that was fun, goodbye.)', 'Will Wood', 0, 'Angie'),
(770, 13, 662, 'g4UGCaLg2SY', 'Laplace’s Angel (Hurt People? Hurt People!)', 'Will Wood', 0, 'Angie'),
(771, 13, 663, '86nwbt0BxbM', '…well, better than the alternative', 'Will Wood', 0, 'Angie'),
(772, 13, 664, 'DvueppU31fM', 'Outliars and Hyppocrates: a fun fact about apples', 'Will Wood', 0, 'Angie'),
(773, 13, 665, 'zbUgoqu-tb4', 'BlackBoxWarrior - OKULTRA', 'Will Wood', 0, 'Angie'),
(774, 13, 666, 'nyIKBT7-a9M', 'Marsha, Thankk You for the Dialectics, but I Need You to Leave', 'Will Wood', 0, 'Angie'),
(775, 13, 667, 'rm9v7ESAFts', 'Love, Me Normally', 'Will Wood', 0, 'Angie'),
(776, 13, 668, 'MX9LreOigJ8', 'Memento Mori: the most important thing in the world', 'Will Wood', 0, 'Angie'),
(777, 13, 669, 'zIIUejDC6xE', 'Tomcat Disposables', 'Will Wood', 0, 'Angie'),
(778, 13, 670, 'AoIhaAL3EQI', 'Becoming the Lastnames', 'Will Wood', 0, 'Angie'),
(779, 13, 671, 'owJD0Iimnes', 'Cicada Days', 'Will Wood', 0, 'Angie'),
(780, 13, 672, '4G0SHlkJhlA', 'Euthanasia', 'Will Wood', 0, 'Angie'),
(781, 13, 673, '-7tQds-Th9s', 'Falling Up', 'Will Wood', 0, 'Angie'),
(782, 13, 674, 'iVK8jzLLuDg', 'That\'s Enough, Let\'s Get You Home.', 'Will Wood', 0, 'Angie'),
(783, 13, 675, 'idvMKvnz5Mk', 'Um, It\'s Kind of a Lot', 'Will Wood', 0, 'Angie'),
(784, 13, 676, 'WeVWxx0-sik', 'Half-Decade Hangover', 'Will Wood', 0, 'Angie'),
(785, 13, 677, 'VutE_9wpd-c', 'Vampire Reference in a Minor Key', 'Will Wood', 0, 'Angie'),
(786, 13, 678, 'H0lm5WN848s', 'You Liked This (Okay, Computer!)', 'Will Wood', 0, 'Angie'),
(787, 13, 679, 'RkHMKUhsBtU', 'The Main Character', 'Will Wood', 0, 'Angie'),
(788, 13, 680, 'eKjbMuOFF3E', 'Against the Kitchen Floor', 'Will Wood', 0, 'Angie'),
(789, 13, 681, 'WTe0abueYqo', 'Sex, Drugs, Rock \'n\' roll', 'Will Wood', 0, 'Angie'),
(790, 13, 682, '93O6EAJrnZc', 'Big Fat Bitchie’s Blueberry Pie, Christmas Tree, and Recreational Jell-O Emporium a.K.a. “Mr. Boy Is on the Roof Again” (From “B.F.B.\'s B-Sides: Bagel Batches, Marsh-Mallows, & Barsh-Mallows)', 'Will Wood', 0, 'Angie'),
(791, 13, 683, 'RHhgHmy0m9k', 'Willard!', 'Will Wood', 0, 'Angie'),
(792, 13, 684, 'Ka9woR5pnk0', 'White Noise', 'Will Wood', 0, 'Angie'),
(793, 13, 685, 'vY_3YrKtUUE', 'Eres Mía', 'Romeo Santos', 0, 'Angie'),
(794, 13, 686, 'erPCW45lHHM', 'Ruler of My Heart (VIVINOS - ALNST Original Soundtrack Part.5)', 'BL8M;Rubyeye', 0, 'Angie'),
(795, 13, 687, 'ugqjcXjpCts', 'Night of Nights', 'RichaadEB', 0, 'Angie'),
(796, 13, 688, 'SweyQg-Jlew', 'Night of Fire', 'RichaadEB;Caleb Hyles;FamilyJules', 0, 'Angie'),
(797, 13, 689, 'ns6e1WWOneY', 'U.N. Owen Was Her?', 'RichaadEB', 0, 'Angie'),
(798, 13, 690, 'SLYGnCcakpw', 'Nuclear Fusion', 'RichaadEB', 0, 'Angie'),
(799, 13, 691, 'vZ0XlpHdh80', 'Lunar Clock Luna Dial', 'RichaadEB;James Fraser', 0, 'Angie'),
(800, 13, 692, '_9SWpnQzjaM', 'Lunatic Princess', 'RichaadEB', 0, 'Angie'),
(801, 13, 693, 'wgl-Vx0r8zQ', 'Flowering Night', 'RichaadEB', 0, 'Angie'),
(802, 13, 694, '2ImLLeIfjME', 'Native Faith', 'RichaadEB', 0, 'Angie'),
(803, 13, 695, 'XhEkXMkVrLw', 'Septette for the Dead Princess', 'RichaadEB', 0, 'Angie'),
(804, 13, 696, '2vdPZkzLNcA', 'Reach for the Moon, Immortal Smoke', 'RichaadEB;Alejandro Hernández', 0, 'Angie'),
(805, 13, 697, 'ZFxDC7CnzvY', 'Beloved Tomboyish Daughter', 'RichaadEB', 0, 'Angie'),
(806, 13, 698, 'eVCIxtS97Pg', 'Necrofantasia', 'RichaadEB', 0, 'Angie'),
(807, 13, 699, 'iYqNd7vZFjk', 'Love Coloured Master Spark', 'RichaadEB', 0, 'Angie'),
(808, 13, 700, '46Mp6Bq1lFM', 'Primordial Beat', 'RichaadEB', 0, 'Angie'),
(809, 13, 701, 'Vc82j7FxdCM', 'Border of Life', 'RichaadEB', 0, 'Angie'),
(810, 13, 702, 'zG325qCKH9o', 'Pure Furies ~ Whereabouts of the Heart', 'RichaadEB', 0, 'Angie'),
(811, 13, 703, 'F4T8JmubdJ8', 'Wind God Girl', 'RichaadEB', 0, 'Angie'),
(812, 13, 704, '3KqgM7w7VHc', 'Doll Judgment ~ The Girl Who Played with People\'s Shapes', 'RichaadEB', 0, 'Angie'),
(813, 13, 705, '6pUDJC3UvIQ', 'The Venerable Ancient Battlefield ~ Suwa Foughten Field', 'RichaadEB', 0, 'Angie'),
(814, 13, 706, 'H7PHyAwjjMk', 'Shanghai Alice of Meiji 17', 'RichaadEB', 0, 'Angie'),
(815, 13, 707, '6lQyfgivAco', 'Hartmann\'s Youkai Girl', 'RichaadEB', 0, 'Angie'),
(816, 13, 708, 'aODIcw8ze3Q', 'Emotional Skyscraper ~ Cosmic Mind', 'RichaadEB', 0, 'Angie'),
(817, 13, 709, 'KXjQyOyP_mw', 'Lullaby of Deserted Hell', 'RichaadEB;Jonny Atma', 0, 'Angie'),
(818, 13, 710, 'nXorMGAnBLo', 'Pierrot of the Star-Spangled Banner', 'RichaadEB', 0, 'Angie'),
(819, 13, 711, 'Uv4eonwpKjc', 'Kobito of the Shining Needle ~ Little Princess', 'RichaadEB;YaboiMatoi', 0, 'Angie'),
(820, 13, 712, 'ugqjcXjpCts', 'Night of Nights', 'RichaadEB', 0, 'Angie'),
(821, 13, 713, 'awc6vDxjtfc', 'Lunatic Eyes ~ Invisible Full Moon', 'RichaadEB', 0, 'Angie'),
(822, 13, 714, '7W312_fHWAc', 'Satori Maiden ~ 3rd Eye', 'RichaadEB', 0, 'Angie'),
(823, 13, 715, 's5_nM3PIV5c', 'Entrusting this World to Idols ~ Idolatrize World', 'RichaadEB', 0, 'Angie'),
(824, 13, 716, 'ZhClc6X1NIQ', 'Bad Apple!! - Japanese Remaster', 'RichaadEB;Cristina Vee', 0, 'Angie'),
(825, 13, 717, '5xc6lQzTUDA', 'Bad Apple!! - English Remaster', 'RichaadEB;Cristina Vee', 0, 'Angie'),
(826, 13, 718, 'CAL4WMpBNs0', 'Your Reality', 'Dan Salvato', 0, 'Angie'),
(827, 13, 719, 'QDYUiCPLtxk', 'In Absentia ΛΟΓΟΣ', 'Heaven Pierce Her', 0, 'Angie'),
(828, 13, 720, 'mxKUFhBKmnw', 'Spiral Out (Keep Going)', 'Heaven Pierce Her', 0, 'Angie'),
(829, 13, 721, 'XY3b4kVAV2Y', 'Never Odd or Even', 'Heaven Pierce Her', 0, 'Angie'),
(830, 13, 722, '_ysPpT7-f4o', 'No Devil Lived On', 'Heaven Pierce Her', 0, 'Angie'),
(831, 13, 723, '9BY8FW1LLqw', 'Mirror Rim', 'Heaven Pierce Her', 0, 'Angie'),
(832, 13, 724, 'gtboglNaLgw', 'The Break (Crimson Glass deComposition)', 'Heaven Pierce Her', 0, 'Angie'),
(833, 13, 725, 'NVqXG3QNav0', 'The Shattering Circle, or: A Charade of Shadeless Ones and Zeroes Rearranged ad Nihilum', 'Heaven Pierce Her', 0, 'Angie'),
(834, 13, 726, 'stpMtEx5zqc', 'Event Horizon (Reach for the Sun and Burn! Burn! Burn!)', 'Heaven Pierce Her', 0, 'Angie'),
(835, 13, 727, 'BvPrRkK1I6k', 'The Fall', 'Heaven Pierce Her', 0, 'Angie'),
(836, 13, 728, '3wp6C1HRTXA', 'Dune Eternal', 'Heaven Pierce Her', 0, 'Angie'),
(837, 13, 729, 'HUYZu3LpMHM', 'Sands of Tide', 'Heaven Pierce Her', 0, 'Angie'),
(838, 13, 730, 'm-WMLdMOQwE', 'Dancer in the Darkness', 'Heaven Pierce Her', 0, 'Angie'),
(839, 13, 731, 'bcOUS1o6bwM', 'Duel (Versus Reprise)', 'Heaven Pierce Her', 0, 'Angie'),
(840, 13, 732, 'ZIgtEW8jOt4', 'Deep Blue', 'Heaven Pierce Her', 0, 'Angie'),
(841, 13, 733, 'JviF4u8qSyk', 'He Is the Light in My Darkness', 'Heaven Pierce Her', 0, 'Angie'),
(842, 13, 734, 'LNNoFHH5UDQ', 'Death Odyssey', 'Heaven Pierce Her', 0, 'Angie'),
(843, 13, 735, 'lQ8Hqtgyc5U', 'Death Odyssey Aftermath', 'Heaven Pierce Her', 0, 'Angie'),
(844, 13, 736, 'sBCS5pRP_xk', 'The Abyss and the Serpent', 'Heaven Pierce Her', 0, 'Angie'),
(845, 13, 737, 'jp8YBW6RsMc', 'Chord of the Crooked Saints', 'Heaven Pierce Her', 0, 'Angie'),
(846, 13, 738, 'm5ra-w1xct8', 'Altars of Apostasy', 'Heaven Pierce Her', 0, 'Angie'),
(847, 13, 739, 'BSpR0DJEgxM', 'The Death of God\'s Will', 'Heaven Pierce Her', 0, 'Angie'),
(848, 13, 740, 'g5EhlFvqiM8', 'Silence. Introspection.', 'Heaven Pierce Her', 0, 'Angie'),
(849, 13, 741, '917xijnpJVw', 'The Fire Is Gone (For Piano, Saxophone and Trumpet)', 'Heaven Pierce Her', 0, 'Angie'),
(850, 13, 742, 'h4eXX5LGxuI', 'Into the Fire', 'Heaven Pierce Her', 0, 'Angie'),
(851, 13, 743, 'tkLVbp0IH_E', 'Unstoppable Force', 'Heaven Pierce Her', 0, 'Angie'),
(852, 13, 744, 'RbXEFL9RIWg', 'Cerberus', 'Heaven Pierce Her', 0, 'Angie'),
(853, 13, 745, '2rzKRG1Jfzs', 'A Thousand Greetings', 'Heaven Pierce Her', 0, 'Angie'),
(854, 13, 746, 'pUqKjJJTvj4', 'A Shattered Illusion', 'Heaven Pierce Her', 0, 'Angie'),
(855, 13, 747, 'ukEE6OPltQA', 'A Complete and Utter Destruction of the Senses', 'Heaven Pierce Her', 0, 'Angie'),
(856, 13, 748, 'jvXwKCex990', 'Sanctuary in the Garden of the Mind', 'Heaven Pierce Her', 0, 'Angie'),
(857, 13, 749, 'AX9pqyjYZBM', 'Castle Vein', 'Heaven Pierce Her', 0, 'Angie'),
(858, 13, 750, 'BhEnjxYLBYo', 'Versus', 'Heaven Pierce Her', 0, 'Angie'),
(859, 13, 751, 'c4HovBdrsYM', 'Cold Winds', 'Heaven Pierce Her', 0, 'Angie'),
(860, 13, 752, 'rD3ONF40PYw', 'Requiem', 'Heaven Pierce Her', 0, 'Angie'),
(861, 13, 753, '0eGf62Isnwo', 'Panic Betrayer', 'Heaven Pierce Her', 0, 'Angie'),
(862, 13, 754, 'FIuia_wqvZw', 'In the Presence of a King', 'Heaven Pierce Her', 0, 'Angie'),
(863, 13, 755, '0oKVuDnNX-4', 'Guts', 'Heaven Pierce Her', 0, 'Angie'),
(864, 13, 756, 'kzbbO_lyZ94', 'Glory', 'Heaven Pierce Her', 0, 'Angie'),
(865, 13, 757, 'HZY-9V1YX_U', 'Divine Intervention', 'Heaven Pierce Her', 0, 'Angie'),
(866, 13, 758, 'ZDStF3H4L-o', 'Disgrace. Humiliation.', 'Heaven Pierce Her', 0, 'Angie'),
(867, 13, 759, 'VjzFO06Xrzw', 'The Fire Is Gone (For Music Box)', 'Heaven Pierce Her', 0, 'Angie'),
(868, 13, 760, 'KtGOy5nK8w8', 'Run Rabbit', 'Mollie Elizabeth', 0, 'Angie');

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
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `periodicidad` varchar(10) NOT NULL DEFAULT 'ninguna'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `user_presence`
--

CREATE TABLE `user_presence` (
  `user_id` int(11) NOT NULL,
  `last_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_presence`
--

INSERT INTO `user_presence` (`user_id`, `last_at`) VALUES
(2, '2026-06-07 16:28:38');

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
(2, 'player', '{\"playlistId\":13,\"trackIndex\":329,\"volume\":100}', '2026-06-07 16:27:07'),
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
-- Indexes for table `listening_invites`
--
ALTER TABLE `listening_invites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_to` (`to_user_id`,`status`),
  ADD KEY `idx_session` (`session_id`);

--
-- Indexes for table `listening_participants`
--
ALTER TABLE `listening_participants`
  ADD PRIMARY KEY (`session_id`,`user_id`),
  ADD KEY `idx_user` (`user_id`,`left_at`),
  ADD KEY `idx_last_seen` (`last_seen_at`);

--
-- Indexes for table `listening_sessions`
--
ALTER TABLE `listening_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_host` (`host_user_id`,`closed_at`),
  ADD KEY `idx_updated` (`updated_at`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `mascota_memoria`
--
ALTER TABLE `mascota_memoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_memoria` (`user_id`,`clave`);

--
-- Indexes for table `mascota_objetos`
--
ALTER TABLE `mascota_objetos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `mascota_vinculos`
--
ALTER TABLE `mascota_vinculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_vinculo` (`mascota_id_a`,`mascota_id_b`);

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
-- Indexes for table `music_album_actions`
--
ALTER TABLE `music_album_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_year` (`user_id`,`played_at`),
  ADD KEY `idx_user_album` (`user_id`,`album_title`);

--
-- Indexes for table `music_extras`
--
ALTER TABLE `music_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mex_user` (`user_id`);

--
-- Indexes for table `music_plays`
--
ALTER TABLE `music_plays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_year` (`user_id`,`played_at`),
  ADD KEY `idx_user_video` (`user_id`,`video_id`);

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
-- Indexes for table `user_presence`
--
ALTER TABLE `user_presence`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_last_at` (`last_at`);

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
-- AUTO_INCREMENT for table `listening_invites`
--
ALTER TABLE `listening_invites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listening_sessions`
--
ALTER TABLE `listening_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `list_items`
--
ALTER TABLE `list_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mascota_memoria`
--
ALTER TABLE `mascota_memoria`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mascota_objetos`
--
ALTER TABLE `mascota_objetos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mascota_vinculos`
--
ALTER TABLE `mascota_vinculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `music_album_actions`
--
ALTER TABLE `music_album_actions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `music_extras`
--
ALTER TABLE `music_extras`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `music_plays`
--
ALTER TABLE `music_plays`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `playlist_invites`
--
ALTER TABLE `playlist_invites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `playlist_tracks`
--
ALTER TABLE `playlist_tracks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=869;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- Constraints for table `mascotas`
--
ALTER TABLE `mascotas`
  ADD CONSTRAINT `mascotas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mascota_memoria`
--
ALTER TABLE `mascota_memoria`
  ADD CONSTRAINT `mascota_memoria_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mascota_objetos`
--
ALTER TABLE `mascota_objetos`
  ADD CONSTRAINT `mascota_objetos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

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
