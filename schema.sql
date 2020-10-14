SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
--
-- Table structure for table `telegram_bot_messages`
--

CREATE TABLE `telegram_bot_messages` (
  `id` int(11) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `real_timestamp` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `message_text` text NOT NULL,
  `raw_postdata` text NOT NULL,
  `is_outgoing_message` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `telegram_bot_users`
--

CREATE TABLE `telegram_bot_users` (
  `user_id` int(11) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `username` text NOT NULL,
  `can_use_bot` int(11) NOT NULL,
  `current_status` int(11) NOT NULL DEFAULT '0',
  `kak_uznal` text,
  `city` text,
  `wants_to_help` tinyint(1) DEFAULT NULL,
  `age_gte_18` tinyint(1) DEFAULT NULL,
  `accept_risk` tinyint(1) DEFAULT NULL,
  `agree_personal_data` tinyint(1) DEFAULT NULL,
  `comment` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `telegram_bot_messages`
--
ALTER TABLE `telegram_bot_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `telegram_bot_users`
--
ALTER TABLE `telegram_bot_users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `current_status` (`current_status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `telegram_bot_messages`
--
ALTER TABLE `telegram_bot_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
