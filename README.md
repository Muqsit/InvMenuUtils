# InvMenuUtils
A utility virion for the [InvMenu](https://github.com/Muqsit/InvMenu) virion implementing some commonly used procedures.

## Assigning multiple listeners to one `InvMenu`
Multiple listeners have different behaviour for readonly and non-readonly InvMenu instances.
For non-readonly InvMenu instances, listeners are prioritised in the order they were passed to the `InvMenuListenerUtils::multiple()` method.
A listener will not be executed if the previous listener cancelled the transaction (i.e returned false).
```php
$menu = InvMenu::create(InvMenu::TYPE_CHEST);
$menu->setListener(InvMenuListenerUtils::multiple(
	$menu,
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		echo "This listener is called first" . TextFormat::EOL;
		return true;
	},
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		echo "This listener is called second." . TextFormat::EOL;
		return false;
	},
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		// This listener is not called as the previous listener cancelled the transaction
		// by returning false.
		return true;
	}
));
```
For readonly InvMenu instances, all listeners will be executed as the transaction is anyway forcefully cancelled.
```php

$menu = InvMenu::create(InvMenu::TYPE_CHEST);
$menu->readonly();
$menu->setListener(InvMenuListenerUtils::multiple(
	$menu,
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : void{
		echo "This listener is called first" . TextFormat::EOL;
	},
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : void{
		echo "This listener is called second." . TextFormat::EOL;
	},
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : void{
		echo "This listener is called third." . TextFormat::EOL;
	}
));
```

## Assigning slot-specific listeners to an `InvMenu`
Listen or handle specific slots, or handle each slot separately.<br>
Index your listeners to the slot you'd like the listener to handle/listen.<br>
TIP: Use index `-1` to "catch-all" (fallback).
```php
$menu->setListener(InvMenuListenerUtils::slotSpecific($menu, [
	8 => function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		$player->sendMessage("You clicked slot #8");
		return true;
	},
	16 => function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		$player->sendMessage("You clicked slot #16");
		return true;
	}
	-1 => function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		$player->sendMessage("Fallback: You clicked slot #" . $action->getSlot());
		return true;
	}
]));
```

## Blacklisting specific slots
**NOTE:** This method is applicable ONLY to non-readonly InvMenu instances.
Blacklisting an array of slots disallows players to modify those slots.
```php
$menu = InvMenu::create(InvMenu::TYPE_CHEST);
$menu->setListener(InvMenuListenerUtils::blacklistSlots([0, 4, 8]));
```
You can even use this in combination with `InvMenuListenerUtils::multiple()`.
```php
$menu = InvMenu::create(InvMenu::TYPE_CHEST);
$menu->setListener(InvMenuListenerUtils::multiple(
	$menu,
	InvMenuListenerUtils::blacklistSlots([0, 1, 2]),
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		$player->sendMessage("You didn't click any of these slots: 0, 1, 2");
		return true;
	}
));
```

## Whitelisting specific slots
**NOTE:** This method is applicable ONLY to non-readonly InvMenu instances.
Whitelisting an array of slots allows players to modify ONLY those slots.
```php
$menu = InvMenu::create(InvMenu::TYPE_CHEST);
$menu->setListener(InvMenuListenerUtils::whitelistSlots([0, 4, 8]));
```
Similar to `InvMenuListenerUtils::blacklistSlots()`, you can use this in combination with `InvMenuListenerUtils::multiple()`.
```php
$menu = InvMenu::create(InvMenu::TYPE_CHEST);
$menu->setListener(InvMenuListenerUtils::multiple(
	$menu,
	InvMenuListenerUtils::whitelistSlots([0, 1, 2]),
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		$player->sendMessage("You click one of these slots: 0, 1, 2");
		return true;
	}
));
```

## Filtering items with specific NBT tags
**NOTE:** This method is applicable ONLY to non-readonly InvMenu instances.
Filter items only with a specific NBT tag on them to be taken out of the inventory.
```php
$menu->setListener(InvMenuListenerUtils::onlyItemsWithTag("CustomItem", StringTag::class));
```
Filter items only without a specific NBT tag on them to be taken out of the inventory.
```php
$menu->setListener(InvMenuListenerUtils::onlyItemsWithoutTag("Button", ByteTag::class));
```
Similar to `InvMenuListenerUtils::whitelistSlots()` and `InvMenuListenerUtils::blacklistSlots()`, this can be used in combination with `InvMenuListenerUtils::multiple()`.
```php
$menu->setListener(InvMenuListenerUtils::multiple(
	$menu,
	InvMenuListenerUtils::onlyItemsWithTag("Button", ByteTag::class),
	function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
		$player->sendMessage("You clicked a button!");
		return false;
	}
));
```
