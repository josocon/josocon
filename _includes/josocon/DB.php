<?php /* -*- tab-width: 4; indent-tabs-mode: t -*-
vim: ts=4 noet ai */

namespace josocon;

/**
	Copyright 2019 (C) 東大女装子コンテスト実行委員会

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	https://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.

	@license Apache-2.0
	@file
*/

use josocon\entity\Event;
use josocon\entity\Item;
use josocon\entity\ItemPicture;
use josocon\entity\Subevent;
use josocon\entity\User;

use josocon\data\ArrayList;
use josocon\data\StringList;


class DB
{
	const SCHEMA_PATH = __DIR__ . '/../schema.sql';
	
	private $dbh;
	private $getUsers;
	private $getEvents;
	private $getEventByName;
	private $getSubevents;
	private $getItems;
	
	public function __construct (string $path)
	{
		$dsn = "sqlite:$path";
		$this->dbh = new \PDO ($dsn);
		$this->dbh->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->dbh->beginTransaction ();
		$this->dbh->exec (\file_get_contents (self::SCHEMA_PATH));
		$this->dbh->commit ();
	}
	
	public static function createUserList (User ... $items): ArrayList
	{
		return ArrayList::getInstance (User::class, ... $items);
	}
	
	public static function createEventList (Event ... $items): ArrayList
	{
		return ArrayList::getInstance (Event::class, ... $items);
	}
	
	public static function createSubeventList (Subevent ... $items): ArrayList
	{
		return ArrayList::getInstance (Subevent::class, ... $items);
	}
	
	public static function createItemList (Item ... $items): ArrayList
	{
		return ArrayList::getInstance (Item::class, ... $items);
	}
	
	public static function createItemPictureList (ItemPicture ... $items): ArrayList
	{
		return ArrayList::getInstance (ItemPicture::class, ... $items);
	}
	
	public function getUsers ($page = 0, $size = 10): ArrayList // User
	{
		if (!isset ($this->getUsers)) {
			$this->getUsers = $this->dbh->prepare ('SELECT * FROM `user` ORDER BY user_id DESC LIMIT :offset, :limit');
		}
		
		$limit = $size;
		$offset = $page * $size;
		$this->getUsers->execute ([':limit' => $limit, ':offset' => $offset]);
		$rows = $this->getUsers->fetchAll (\PDO::FETCH_OBJ);
		$users = [];
		foreach ($rows as $row) {
			$user = new User;
			$user->id = $row->user_id;
			$user->name = $row->user_name;
			$user->long_name = $row->user_long_name;
			$user->hash = $row->user_hash;
			$user->description = $row->user_description;
			$users[] = $user;
		}
		return self::createUserList (... $users);
	}
	
	public function getEvents ($page = 0, $size = 10): ArrayList // Event
	{
		if (!isset ($this->getEvents)) {
			$this->getEvents = $this->dbh->prepare ('SELECT * FROM `event` ORDER BY event_id DESC LIMIT :offset, :limit');
		}
		
		$limit = $size;
		$offset = $page * $size;
		$this->getEvents->execute ([':limit' => $limit, ':offset' => $offset]);
		$rows = $this->getEvents->fetchAll (\PDO::FETCH_OBJ);
		$events = [];
		foreach ($rows as $row) {
			$event = new Event;
			$event->id = $row->event_id;
			$event->name = $row->event_name;
			$event->title = $row->event_title;
			$event->description = $row->event_description;
			$event->vote_status = $row->event_vote_status;
			$events[] = $event;
		}
		return self::createEventList (... $events);
	}
	
	public function getEventByName (string $name): ?Event
	{
		if (!isset ($this->getEventByName)) {
			$this->getEventByName = $this->dbh->prepare ('SELECT * FROM `event` WHERE event_name = :name');
		}
		
		$this->getEventByName->execute ([':name' => $name]);
		$row = $this->getEventByName->fetch (\PDO::FETCH_OBJ);
		if (!$row) {
			return null;
		}
		$event = new Event;
		$event->id = $row->event_id;
		$event->name = $row->event_name;
		$event->title = $row->event_title;
		$event->description = $row->event_description;
		$event->vote_status = $row->event_vote_status;
		return $event;
	}
	
	public function getEventById (int $id): ?Event
	{
		if (!isset ($this->getEventById)) {
			$this->getEventById = $this->dbh->prepare ('SELECT * FROM `event` WHERE event_id = :id');
		}
		
		$this->getEventById->execute ([':id' => $id]);
		$row = $this->getEventById->fetch (\PDO::FETCH_OBJ);
		if (!$row) {
			return null;
		}
		$event = new Event;
		$event->id = $row->event_id;
		$event->name = $row->event_name;
		$event->title = $row->event_title;
		$event->description = $row->event_description;
		$event->vote_status = $row->event_vote_status;
		return $event;
	}
	
	public function getSubevents (Event $event): ArrayList // Subevent
	{
		if (!isset ($this->getSubevents)) {
			$this->getSubevents = $this->dbh->prepare ('SELECT * FROM `subevent` WHERE event_id = :event_id');
		}
		
		$this->getSubevents->execute ([':event_id' => $event->id]);
		$rows = $this->getSubevents->fetchAll (\PDO::FETCH_OBJ);
		$items = [];
		foreach ($rows as $row) {
			$item = new Subevent;
			$item->id = $row->subevent_id;
			$item->event_id = $row->event_id;
			$item->title = $row->subevent_title;
			$item->description = $row->subevent_description;
			$items[] = $item;
		}
		return self::createSubeventList (... $items);
	}
	
	public function getItems (Event $event): ArrayList // Item
	{
		if (!isset ($this->getItems)) {
			$this->getItems = $this->dbh->prepare ('SELECT * FROM `item` WHERE event_id = :event_id');
		}
		
		$this->getItems->execute ([':event_id' => $event->id]);
		$rows = $this->getItems->fetchAll (\PDO::FETCH_OBJ);
		$items = [];
		foreach ($rows as $row) {
			$item = new Item;
			$item->id = $row->item_id;
			$item->event_id = $row->event_id;
			$item->name = $row->item_name;
			$item->description = $row->item_description;
			$item->vote_count = $row->item_vote_count;
			$items[] = $item;
		}
		return self::createItemList (... $items);
	}
	
	public function getItemPictures (Item $item): ArrayList // ItemPicture
	{
		if (!isset ($this->getItemPictures)) {
			$this->getItemPictures = $this->dbh->prepare ('SELECT * FROM `item_picture` WHERE item_id = :item_id');
		}
		
		$this->getItemPictures->execute ([':item_id' => $item->id]);
		$rows = $this->getItemPictures->fetchAll (\PDO::FETCH_OBJ);
		$items = self::createItemPictureList ();
		foreach ($rows as $row) {
			$item = new ItemPicture;
			$item->id = $row->item_picture_id;
			$item->item_id = $row->item_id;
			$item->uri = $row->item_picture_uri;
		}
		return $items;
	}
	
	public function getUserByName (string $name): ?User
	{
		if (!isset ($this->getUserByName)) {
			$this->getUserByName = $this->dbh->prepare ('SELECT * FROM `user` WHERE user_name = :name');
		}
		
		$this->getUserByName->execute ([':name' => $name]);
		$row = $this->getUserByName->fetch (\PDO::FETCH_OBJ);
		if (!$row) {
			return null;
		}
		$item = new User;
		$item->id = $row->user_id;
		$item->name = $row->user_name;
		$item->long_name = $row->user_long_name;
		$item->hash = $row->user_hash;
		$item->description = $row->user_description;
		return $item;
	}
	
	public function addUser (string $name, string $password): void
	{
		$hash = User::hashPassword ($password);
		if (!isset ($this->addUser)) {
			$this->addUser = $this->dbh->prepare ('INSERT INTO `user` (user_name, user_hash) VALUES (:name, :hash)');
		}
		
		$this->addUser->execute ([':name' => $name, ':hash' => $hash]);
	}
	
	public function updateUser (User $user): void
	{
		if (!isset ($this->updateUser)) {
			$this->updateUser = $this->dbh->prepare ('UPDATE `user` SET user_name = :name, user_long_name = :long_name, user_hash = :hash, user_description = :description WHERE user_id = :id');
		}
		
		$this->updateUser->execute ([
			':id' => $user->id,
			':name' => $user->name,
			':long_name' => $user->long_name,
			':hash' => $user->hash,
			':description' => $user->description,
		]);
	}
	
	public function updateEvent (Event $event): void
	{
		if (!isset ($this->updateEvent)) {
			$this->updateEvent = $this->dbh->prepare ('UPDATE `event` SET event_name = :name, event_title = :title, event_description = :description, event_vote_status = :vote_status WHERE event_id = :id');
		}
		
		$this->updateEvent->execute ([
			':id' => $event->id,
			':name' => $event->name,
			':title' => $event->title,
			':vote_status' => $event->vote_status,
			':description' => $event->description,
		]);
	}
	
	public function updateSubevent (Subevent $subevent): void
	{
		if (!isset ($this->updateSubevent)) {
			$this->updateSubevent = $this->dbh->prepare ('UPDATE `subevent` SET subevent_title = :title, subevent_description = :description WHERE subevent_id = :id');
		}
		
		$this->updateSubevent->execute ([
			':id' => $subevent->id,
			':title' => $subevent->title,
			':description' => $subevent->description,
		]);
	}
	
	public function updateItem (Item $item): void
	{
		if (!isset ($this->updateItem)) {
			$this->updateItem = $this->dbh->prepare ('UPDATE `item` SET item_name = :name, item_description = :description, item_vote_count = :vote_count WHERE item_id = :id');
		}
		
		$this->updateItem->execute ([
			':id' => $item->id,
			':name' => $item->name,
			':vote_count' => $item->vote_count,
			':description' => $item->description,
		]);
	}
	
	public function addEvent (string $name, string $title = ''): void
	{
		if (!isset ($this->addEvent)) {
			$this->addEvent = $this->dbh->prepare ('INSERT INTO `event` (event_name, event_title) VALUES (:name, :title)');
		}
		
		if ('' === $title) {
			$title = $name;
		}
		$this->addEvent->execute ([
			':name' => $name,
			':title' => $title,
		]);
	}
	
	public function addSubevent (Event $event, string $title = ''): void
	{
		if (!isset ($this->addSubevent)) {
			$this->addSubevent = $this->dbh->prepare ('INSERT INTO `subevent` (event_id, subevent_title) VALUES (:event_id, :title)');
		}
		
		$this->addSubevent->execute ([
			':event_id' => $event->id,
			':title' => $title,
		]);
	}
	
	public function addItemPicture (Item $item, string $uri, string $description = ''): void
	{
		if (!isset ($this->addItemPicture)) {
			$this->addItemPicture = $this->dbh->prepare ('INSERT INTO `item_picture` (item_id, item_picture_uri, item_picture_description) VALUES (:item_id, :uri, :description)');
		}
		
		$this->addItemPicture->execute ([
			':item_id' => $item->id,
			':uri' => $uri,
			':description' => $description,
		]);
	}
	
	public function removeItemPictureByUri (Item $item, string $uri): void
	{
		if (!isset ($this->removeItemPictureByUri)) {
			$this->removeItemPictureByUri = $this->dbh->prepare ('DELETE FROM `item` WHERE item_id = :item_id AND item_picture_uri = :uri');
		}
		
		$this->removeItemPictureByUri->execute ([
			':item_id' => $item->id,
			':uri' => $uri,
		]);
	}
	
	public function removeItemPicture (ItemPicture $picture): void
	{
		if (!isset ($this->removeItemPicture)) {
			$this->removeItemPicture = $this->dbh->prepare ('DELETE FROM `item` WHERE item_picture_id = :id');
		}
		
		$this->removeItemPicture->execute ([
			':id' => $picture->id,
		]);
	}
	
	public function updateItemPicture (ItemPicture $item): void
	{
		if (!isset ($this->updateItemPicture)) {
			$this->updateItemPicture = $this->dbh->prepare ('UPDATE `item_picture` SET item_picture_uri = :uri, item_picture_description = :description WHERE item_picture_id = :id');
		}
		
		$this->updateItemPicture->execute ([
			':id' => $item->id,
			':uri' => $item->uri,
			':description' => $item->description,
		]);
	}
}

