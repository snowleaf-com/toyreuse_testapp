-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost:8889
-- 生成日時: 2024 年 10 月 26 日 14:24
-- サーバのバージョン： 8.0.35
-- PHP のバージョン: 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `akachan`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `category`
--

CREATE TABLE `category` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `community`
--

CREATE TABLE `community` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `made_by_id` int NOT NULL,
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_flg` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `c_join`
--

CREATE TABLE `c_join` (
  `id` int NOT NULL,
  `community_id` int NOT NULL,
  `user_id` int NOT NULL,
  `join_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `c_message`
--

CREATE TABLE `c_message` (
  `id` int NOT NULL,
  `community_id` int NOT NULL,
  `from_user` int NOT NULL,
  `message` text NOT NULL,
  `send_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_date` datetime NOT NULL,
  `delete_flg` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `members`
--

CREATE TABLE `members` (
  `id` int NOT NULL,
  `mail` varchar(255) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `userkananame` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bornyear` int NOT NULL,
  `bornmonth` int NOT NULL,
  `bornday` int NOT NULL,
  `zip` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `number` varchar(20) NOT NULL,
  `pic` varchar(255) DEFAULT NULL,
  `delete_flg` tinyint(1) NOT NULL DEFAULT '0',
  `login_time` datetime DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `update_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `pre_members`
--

CREATE TABLE `pre_members` (
  `id` int NOT NULL,
  `pre_mail` varchar(255) NOT NULL,
  `urltoken` varchar(255) NOT NULL,
  `flg` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `pre_passmail_edit`
--

CREATE TABLE `pre_passmail_edit` (
  `id` int NOT NULL,
  `urltoken` varchar(255) NOT NULL,
  `userid` int NOT NULL,
  `mail` varchar(255) NOT NULL,
  `flg` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `pre_pass_edit`
--

CREATE TABLE `pre_pass_edit` (
  `id` int NOT NULL,
  `pre_mail` varchar(255) NOT NULL,
  `urltoken` varchar(255) NOT NULL,
  `flg` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int NOT NULL,
  `comment` text NOT NULL,
  `price` int NOT NULL,
  `pic1` varchar(255) DEFAULT NULL,
  `pic2` varchar(255) DEFAULT NULL,
  `pic3` varchar(255) DEFAULT NULL,
  `user_id` int NOT NULL,
  `delete_flg` tinyint(1) NOT NULL DEFAULT '0',
  `bought_flg` tinyint(1) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `p_board`
--

CREATE TABLE `p_board` (
  `id` int NOT NULL,
  `sale_user` int NOT NULL,
  `buy_user` int NOT NULL,
  `product_id` int NOT NULL,
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fin_flg` tinyint(1) NOT NULL DEFAULT '0',
  `delete_flg` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `p_favorite`
--

CREATE TABLE `p_favorite` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `p_message`
--

CREATE TABLE `p_message` (
  `id` int NOT NULL,
  `p_board_id` int NOT NULL,
  `to_user` int NOT NULL,
  `from_user` int NOT NULL,
  `msg` text NOT NULL,
  `send_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_date` datetime NOT NULL,
  `delete_flg` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `community`
--
ALTER TABLE `community`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `c_join`
--
ALTER TABLE `c_join`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `c_message`
--
ALTER TABLE `c_message`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail` (`mail`);

--
-- テーブルのインデックス `pre_members`
--
ALTER TABLE `pre_members`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `pre_passmail_edit`
--
ALTER TABLE `pre_passmail_edit`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `pre_pass_edit`
--
ALTER TABLE `pre_pass_edit`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `p_board`
--
ALTER TABLE `p_board`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `p_favorite`
--
ALTER TABLE `p_favorite`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `p_message`
--
ALTER TABLE `p_message`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `community`
--
ALTER TABLE `community`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `c_join`
--
ALTER TABLE `c_join`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `c_message`
--
ALTER TABLE `c_message`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `members`
--
ALTER TABLE `members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `pre_members`
--
ALTER TABLE `pre_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `pre_passmail_edit`
--
ALTER TABLE `pre_passmail_edit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `pre_pass_edit`
--
ALTER TABLE `pre_pass_edit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `p_board`
--
ALTER TABLE `p_board`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `p_favorite`
--
ALTER TABLE `p_favorite`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `p_message`
--
ALTER TABLE `p_message`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
