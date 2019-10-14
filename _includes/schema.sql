-- -*- tab-width: 4; indent-tabs-mode: t -*-
-- vim: ts=4 noet ai

-- データベース構造 (SQLite 3)
--
-- Copyright 2019 (C) 東大女装子コンテスト実行委員会
--
-- Licensed under the Apache License, Version 2.0 (the "License");
-- you may not use this file except in compliance with the License.
-- You may obtain a copy of the License at
--
-- https://www.apache.org/licenses/LICENSE-2.0
--
-- Unless required by applicable law or agreed to in writing, software
-- distributed under the License is distributed on an "AS IS" BASIS,
-- WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
-- See the License for the specific language governing permissions and
-- limitations under the License.
--
-- @license Apache-2.0
-- @file


-- 関係者アカウント
create table if not exists `user`
(
	user_id integer primary key,
	user_name blob unique,
	user_long_name blob default '',
	user_hash blob not null,
	user_description blob default ''
);


-- e.g. 2019駒場祭
create table if not exists `event`
(
	event_id integer primary key,
	event_name blob unique,
	event_title blob default '',
	event_vote_status int default 0,
	event_description blob default ''
);


-- e.g. じょそこんステージ、カフェ
create table if not exists `subevent`
(
	subevent_id integer primary key,
	event_id integer not null,
	subevent_title blob default '',
	subevent_description blob default ''
);


-- i.e. 候補者
create table if not exists `item`
(
	item_id integer primary key,
	event_id integer not null,
	item_name blob default '',
	item_description blob default '',
	item_vote_count integer default 0
);


-- i.e. 候補者の写真
create table if not exists `item_picture`
(
	item_picture_id integer primary key,
	item_id integer not null,
	item_picture_uri blob not null,
	item_picture_description blob default ''
);

