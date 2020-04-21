<?php

/*
 * InvMenuUtils
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Muqsit
 * @link http://github.com/Muqsit
*/

declare(strict_types=1);

namespace muqsit\invmenuutils;

use Closure;
use Ds\Set;
use muqsit\invmenu\InvMenu;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\Player;

final class InvMenuListenerUtils{

	/**
	 * Assign multiple listeners in order of their priority (the first
	 * listener will be called first).
	 *
	 * @param InvMenu $menu
	 * @param Closure ...$listeners
	 * @return Closure
	 */
	public static function multiple(InvMenu $menu, Closure ...$listeners) : Closure{
		return $menu->isReadonly() ? self::multipleReadonly(...$listeners) : self::multipleReadWrite(...$listeners);
	}

	public static function multipleReadonly(Closure ...$listeners) : Closure{
		return static function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) use($listeners) : void{
			foreach($listeners as $listener){
				$listener($player, $itemClicked, $itemClickedWith, $action);
			}
		};
	}

	public static function multipleReadWrite(Closure ...$listeners) : Closure{
		return static function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) use($listeners) : bool{
			foreach($listeners as $listener){
				if(!$listener($player, $itemClicked, $itemClickedWith, $action)){
					return false;
				}
			}
			return true;
		};
	}

	/**
	 * An array of listeners indexed by the slot they should listen to.
	 * Use index -1 for a "catch-all".
	 *
	 * @param InvMenu $menu
	 * @param array<int, Closure> $listeners
	 * @return Closure
	 */
	public static function slotSpecific(InvMenu $menu, array $listeners) : Closure{
		return $menu->isReadonly() ? self::slotSpecificReadonly($listeners) : self::slotSpecificReadWrite($listeners);
	}

	public static function slotSpecificReadonly(array $listeners) : Closure{
		return static function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) use($listeners) : void{
			$listener = $listeners[$action->getSlot()] ?? $listeners[-1] ?? null;
			if($listener !== null){
				$listener($player, $itemClicked, $itemClickedWith, $action);
			}
		};
	}

	public static function slotSpecificReadWrite(array $listeners) : Closure{
		return static function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) use($listeners) : bool{
			$listener = $listeners[$action->getSlot()] ?? $listeners[-1] ?? null;
			return $listener === null || $listener($player, $itemClicked, $itemClickedWith, $action);
		};
	}

	/**
	 * @param int[] $slots
	 * @return Closure
	 */
	public static function blacklistSlots(array $slots) : Closure{
		$blacklist = new Set($slots);
		return static function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) use($blacklist) : bool{
			return !$blacklist->contains($action->getSlot());
		};
	}

	/**
	 * @param int[] $slots
	 * @return Closure
	 */
	public static function whitelistSlots(array $slots) : Closure{
		$whitelist = new Set($slots);
		return static function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) use($whitelist) : bool{
			return $whitelist->contains($action->getSlot());
		};
	}

	/**
	 * @param string $name
	 * @param string $expectedClass
	 * @return Closure
	 */
	public static function onlyItemsWithTag(string $name, string $expectedClass = NamedTag::class) : Closure{
		return static function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) use($name, $expectedClass) : bool{
			return $itemClicked->getNamedTag()->hasTag($name, $expectedClass);
		};
	}

	/**
	 * @param string $name
	 * @param string $expectedClass
	 * @return Closure
	 */
	public static function onlyItemsWithoutTag(string $name, string $expectedClass = NamedTag::class) : Closure{
		return static function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) use($name, $expectedClass) : bool{
			return !$itemClicked->getNamedTag()->hasTag($name, $expectedClass);
		};
	}
}