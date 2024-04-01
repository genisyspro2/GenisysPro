<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\ItemFrame as TileItemFrame;
use pocketmine\tile\Tile;
use function lcg_value;

class ItemFrame extends Flowable{
    protected $id = Block::ITEM_FRAME_BLOCK;

    protected $itemId = Item::ITEM_FRAME;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string{
        return "Item Frame";
    }

    public function onActivate(Item $item, Player $player = null) : bool{
        $tile = $this->level->getTile($this);
        if(!($tile instanceof TileItemFrame)){
            $tile = Tile::createTile(Tile::ITEM_FRAME, $this->getLevelNonNull(), TileItemFrame::createNBT($this));
            if(!($tile instanceof TileItemFrame)){
                return true;
            }
        }

        if($tile->hasItem()){
            $tile->setItemRotation(($tile->getItemRotation() + 1) % 8);
        }elseif(!$item->isNull()){
            $tile->setItem($item->pop());
        }

        return true;
    }

    public function onNearbyBlockChange() : void{
        $sides = [
            1 => Vector3::SIDE_EAST,
            4 => Vector3::SIDE_WEST,
            2 => Vector3::SIDE_NORTH,
            3 => Vector3::SIDE_SOUTH
        ];
        if(isset($sides[$this->meta]) and !$this->getSide($sides[$this->meta])->isSolid()){
            $this->level->useBreakOn($this);
        }
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
        if($face === Vector3::SIDE_DOWN or $face === Vector3::SIDE_UP or !$blockClicked->isSolid()){
            return false;
        }

        $faces = [
            Vector3::SIDE_NORTH => 3,
            Vector3::SIDE_SOUTH => 2,
            Vector3::SIDE_WEST => 1,
            Vector3::SIDE_EAST => 4
        ];

        $this->meta = $faces[$face];
        $this->level->setBlock($blockReplace, $this, true, true);

        $tile = Tile::createTile(Tile::ITEM_FRAME, $this->getLevelNonNull(), TileItemFrame::createNBT($this, $face, $item, $player));
        if($tile instanceof TileItemFrame){
            $tile->setItem($item);
        }

        return true;
    }

    public function getVariantBitmask() : int{
        return 0;
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        $drops = parent::getDropsForCompatibleTool($item);

        $tile = $this->level->getTile($this);
        if($tile instanceof TileItemFrame){
            $tileItem = $tile->getItem();
            if(lcg_value() <= $tile->getItemDropChance() and !$tileItem->isNull()){
                $drops[] = $tileItem;
            }
        }

        return $drops;
    }

    public function isAffectedBySilkTouch() : bool{
        return false;
    }

    public function getHardness() : float{
        return 0.25;
    }
}
