import { BoardTool, ElementType, FixtureType, TrapType, MonsterType } from '@/types/board';

export function defaultElementFlags(type: ElementType): { interactive: boolean; hidden: boolean; passthrough: boolean } {
    switch (type) {
        case ElementType.Door:
            return { interactive: true, hidden: false, passthrough: false };
        case ElementType.SecretDoor:
            return { interactive: true, hidden: true, passthrough: false };
        case ElementType.Monster:
            return { interactive: true, hidden: true, passthrough: false };
        case ElementType.Trap:
            return { interactive: true, hidden: true, passthrough: true };
        case ElementType.Treasure:
            return { interactive: true, hidden: true, passthrough: true };
        default:
            return { interactive: true, hidden: false, passthrough: false };
    }
}

export function toolToElementType(tool: BoardTool): ElementType | null {
    switch (tool) {
        case BoardTool.AddMonster:
            return ElementType.Monster;
        case BoardTool.AddDoor:
            return ElementType.Door;
        case BoardTool.AddSecretDoor:
            return ElementType.SecretDoor;
        case BoardTool.AddTrap:
            return ElementType.Trap;
        case BoardTool.AddTreasure:
            return ElementType.Treasure;
        default:
            return null;
    }
}

// ---- Catalog & labeling helpers (reusable for fixtures, monsters, etc.) ----

export function labelForFixtureType(type: FixtureType, customText?: string): string {
    if (type === FixtureType.Custom) {
        return (customText ?? '').trim() || 'Custom fixture';
    }
    switch (type) {
        case FixtureType.TreasureChest:
            return 'Treasure chest';
        case FixtureType.Chair:
            return 'Chair';
        case FixtureType.Table:
            return 'Table';
        default:
            return 'Fixture';
    }
}

export function labelForTrapType(type: TrapType, customText?: string): string {
    if (type === TrapType.Custom) {
        return (customText ?? '').trim() || 'Custom trap';
    }
    switch (type) {
        case TrapType.Pit:
            return 'Pit trap';
        case TrapType.Spear:
            return 'Spear trap';
        case TrapType.Block:
            return 'Falling block';
        default:
            return 'Trap';
    }
}

export function labelForMonsterType(type: MonsterType, customText?: string): string {
    if (type === MonsterType.Custom) {
        return (customText ?? '').trim() || 'Custom monster';
    }
    switch (type) {
        case MonsterType.Goblin:
            return 'Goblin';
        case MonsterType.Orc:
            return 'Orc';
        case MonsterType.Skeleton:
            return 'Skeleton';
        default:
            return 'Monster';
    }
}
