-- ───────────────────────────────────────────────────────────────────────────
-- MIGRATION 001 — Full schema rebuild for scrapbookOnline
-- ───────────────────────────────────────────────────────────────────────────
-- 1) Drop tablas vacías sin uso
-- 2) Alter `usuarios` (añadir user_key)
-- 3) Crear 19 tablas nuevas (perfil, listas, chat, música, escritorio, temas)
--
-- Decisiones aplicadas:
-- - IDs internos de items/playlists/folders → BIGINT auto. La migración de
--   datos crea tablas auxiliares old_id→new_id para mapear referencias.
-- - JSON nativo en notifications.payload (campos variables por tipo)
-- - InnoDB + utf8mb4 universal
-- - ON DELETE CASCADE en relaciones obvias
-- ───────────────────────────────────────────────────────────────────────────

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ─── 1. Drop tablas no usadas ────────────────────────────────────────────
DROP TABLE IF EXISTS `invitaciones`;
DROP TABLE IF EXISTS `mascotas_invitaciones`;
DROP TABLE IF EXISTS `mascotas`;
DROP TABLE IF EXISTS `emociones`;
DROP TABLE IF EXISTS `fechas_especiales`;

-- ─── 2. Modificar usuarios: añadir user_key (user1, user2, user3) ────────
ALTER TABLE `usuarios`
    ADD COLUMN `user_key` VARCHAR(20) UNIQUE DEFAULT NULL AFTER `id`,
    ADD COLUMN `label` VARCHAR(50) DEFAULT NULL AFTER `username`;

-- ─── 3. Perfil + social ──────────────────────────────────────────────────

CREATE TABLE `profile` (
    `user_id`    INT PRIMARY KEY,
    `quote`      VARCHAR(500) DEFAULT '',
    `bio`        TEXT,
    `pronouns`   VARCHAR(30),
    `age`        TINYINT UNSIGNED,
    `country`    VARCHAR(50),
    `steam`      VARCHAR(200),
    `discord`    VARCHAR(100),
    `twitter`    VARCHAR(100),
    `instagram`  VARCHAR(100),
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `posts` (
    `id`         BIGINT PRIMARY KEY AUTO_INCREMENT,
    `user_id`    INT NOT NULL,
    `text`       VARCHAR(1000) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_posts_user_created` (`user_id`, `created_at` DESC),
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `post_likes` (
    `post_id`  BIGINT NOT NULL,
    `user_id`  INT NOT NULL,
    `liked_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`post_id`, `user_id`),
    INDEX `idx_post_likes_user` (`user_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`)    ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `follows` (
    `follower_id` INT NOT NULL,
    `followee_id` INT NOT NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`follower_id`, `followee_id`),
    INDEX `idx_follows_followee` (`followee_id`),
    FOREIGN KEY (`follower_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`followee_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notifications` (
    `id`           BIGINT PRIMARY KEY AUTO_INCREMENT,
    `user_id`      INT NOT NULL,
    `type`         VARCHAR(40) NOT NULL,
    `from_user_id` INT DEFAULT NULL,
    `payload`      JSON DEFAULT NULL,
    `is_read`      TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notifs_user_read` (`user_id`, `is_read`, `created_at` DESC),
    FOREIGN KEY (`user_id`)      REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_user_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 4. Listas (movies / books / games / music) ──────────────────────────

CREATE TABLE `list_items` (
    `id`               BIGINT PRIMARY KEY AUTO_INCREMENT,
    `owner_id`         INT NOT NULL,
    `category`         ENUM('movies','books','games','music') NOT NULL,
    `title`            VARCHAR(200) NOT NULL,
    `image`            VARCHAR(2000),
    -- status para movies/books/games (NULL en music)
    `status`           ENUM('pending','in-progress','completed') DEFAULT NULL,
    -- music-only
    `music_type`       ENUM('song','album') DEFAULT NULL,
    `artist`           VARCHAR(200) DEFAULT NULL,
    `featured`         TINYINT(1) DEFAULT 0,
    `yt_id`            VARCHAR(40) DEFAULT NULL,
    `spotify_id`       VARCHAR(40) DEFAULT NULL,
    `yt_playlist_id`   VARCHAR(60) DEFAULT NULL,
    `spotify_album_id` VARCHAR(40) DEFAULT NULL,
    -- reseña
    `review_stars`     DECIMAL(2,1) DEFAULT NULL,
    `review_comment`   VARCHAR(1000) DEFAULT NULL,
    `reviewed_at`      TIMESTAMP NULL DEFAULT NULL,
    -- compartido desde otro usuario (NULL si es propio)
    `shared_from`      INT DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_items_owner_cat` (`owner_id`, `category`),
    INDEX `idx_items_shared`     (`shared_from`),
    INDEX `idx_items_reviewed`   (`reviewed_at`),
    FOREIGN KEY (`owner_id`)    REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`shared_from`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `list_item_collaborators` (
    `item_id`  BIGINT NOT NULL,
    `user_id`  INT NOT NULL,
    `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`item_id`, `user_id`),
    INDEX `idx_collab_user` (`user_id`),
    FOREIGN KEY (`item_id`) REFERENCES `list_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `item_invites` (
    `id`              BIGINT PRIMARY KEY AUTO_INCREMENT,
    `to_user_id`      INT NOT NULL,
    `from_user_id`    INT NOT NULL,
    `item_id`         BIGINT NOT NULL,
    `category`        ENUM('movies','books','games','music') NOT NULL,
    `item_title`      VARCHAR(200) NOT NULL,
    `item_image`      VARCHAR(2000),
    `item_music_type` ENUM('song','album') DEFAULT NULL,
    `item_artist`     VARCHAR(200) DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_iinv_to_created` (`to_user_id`, `created_at` DESC),
    FOREIGN KEY (`to_user_id`)   REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 5. Chat ─────────────────────────────────────────────────────────────

CREATE TABLE `chats` (
    `id`          INT PRIMARY KEY AUTO_INCREMENT,
    `user_a`      INT NOT NULL,  -- siempre el id menor del par
    `user_b`      INT NOT NULL,
    `last_seen_a` TIMESTAMP NULL DEFAULT NULL,
    `last_seen_b` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_chats_pair` (`user_a`, `user_b`),
    FOREIGN KEY (`user_a`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_b`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `messages` (
    `id`           BIGINT PRIMARY KEY AUTO_INCREMENT,
    `chat_id`      INT NOT NULL,
    `from_user_id` INT NOT NULL,
    `text`         VARCHAR(2000) NOT NULL,
    `sent_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_msg_chat_sent` (`chat_id`, `sent_at`),
    FOREIGN KEY (`chat_id`)      REFERENCES `chats`(`id`)    ON DELETE CASCADE,
    FOREIGN KEY (`from_user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 6. Música ───────────────────────────────────────────────────────────

CREATE TABLE `playlists` (
    `id`         BIGINT PRIMARY KEY AUTO_INCREMENT,
    `owner_id`   INT NOT NULL,
    `name`       VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pl_owner` (`owner_id`),
    FOREIGN KEY (`owner_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `playlist_tracks` (
    `id`          BIGINT PRIMARY KEY AUTO_INCREMENT,
    `playlist_id` BIGINT NOT NULL,
    `position`    INT NOT NULL,
    `video_id`    VARCHAR(11) NOT NULL,
    `title`       VARCHAR(200) NOT NULL,
    `artist`      VARCHAR(200) DEFAULT '',
    `duration`    INT DEFAULT 0,
    `added_by`    VARCHAR(100) DEFAULT '',
    INDEX `idx_tr_playlist_pos` (`playlist_id`, `position`),
    FOREIGN KEY (`playlist_id`) REFERENCES `playlists`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `playlist_collaborators` (
    `playlist_id` BIGINT NOT NULL,
    `user_id`     INT NOT NULL,
    `added_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`playlist_id`, `user_id`),
    INDEX `idx_plcol_user` (`user_id`),
    FOREIGN KEY (`playlist_id`) REFERENCES `playlists`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`)     REFERENCES `usuarios`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `playlist_invites` (
    `id`           BIGINT PRIMARY KEY AUTO_INCREMENT,
    `to_user_id`   INT NOT NULL,
    `from_user_id` INT NOT NULL,
    `playlist_id`  BIGINT NOT NULL,
    `type`         VARCHAR(40) NOT NULL DEFAULT 'invite',  -- invite | collab-accepted | collab-rejected | collab-left | removed
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_plinv_to_created` (`to_user_id`, `created_at` DESC),
    FOREIGN KEY (`to_user_id`)   REFERENCES `usuarios`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`from_user_id`) REFERENCES `usuarios`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`playlist_id`)  REFERENCES `playlists`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `music_extras` (
    `id`       BIGINT PRIMARY KEY AUTO_INCREMENT,
    `user_id`  INT NOT NULL,
    `video_id` VARCHAR(11) NOT NULL,
    `title`    VARCHAR(200) NOT NULL,
    `artist`   VARCHAR(200) DEFAULT '',
    `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mex_user` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 7. Pareja ───────────────────────────────────────────────────────────

CREATE TABLE `partner_invites` (
    `id`           INT PRIMARY KEY AUTO_INCREMENT,
    `to_user_id`   INT NOT NULL,
    `from_user_id` INT NOT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_pinv_pair` (`to_user_id`, `from_user_id`),
    FOREIGN KEY (`to_user_id`)   REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 8. Temas ────────────────────────────────────────────────────────────

CREATE TABLE `themes` (
    `id`         INT PRIMARY KEY AUTO_INCREMENT,
    `user_id`    INT NOT NULL,
    `name`       VARCHAR(40) NOT NULL,
    `colors`     JSON NOT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_themes_user_name` (`user_id`, `name`),
    INDEX `idx_themes_active` (`user_id`, `is_active`),
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 9. Escritorio ───────────────────────────────────────────────────────

CREATE TABLE `desktop_icons` (
    `user_id`    INT NOT NULL,
    `icon_id`    VARCHAR(60) NOT NULL,  -- archive-icon, calendar-icon, fld-<id_legacy>
    `pos_left`   INT NOT NULL,
    `pos_top`    INT NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `icon_id`),
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `desktop_folders` (
    `id`         BIGINT PRIMARY KEY AUTO_INCREMENT,
    `user_id`    INT NOT NULL,
    `name`       VARCHAR(40) NOT NULL,
    `pos_left`   INT NOT NULL,
    `pos_top`    INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_dfolders_user` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `desktop_folder_items` (
    `folder_id` BIGINT NOT NULL,
    `icon_id`   VARCHAR(60) NOT NULL,   -- mismo dominio que desktop_icons.icon_id
    `position`  INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`folder_id`, `icon_id`),
    FOREIGN KEY (`folder_id`) REFERENCES `desktop_folders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 10. Settings de usuario (player, etc.) ──────────────────────────────

CREATE TABLE `user_settings` (
    `user_id`    INT NOT NULL,
    `key_name`   VARCHAR(60) NOT NULL,
    `value`      JSON NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `key_name`),
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET foreign_key_checks = 1;
