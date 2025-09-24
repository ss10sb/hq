import type { Element, Tile } from '@/types/board';
import type { Hero, Zargon } from '@/types/hero'; // Mirrors PHP enum Domain\Board\GameSession\Constants\Status

// Mirrors PHP enum Domain\Board\GameSession\Constants\Status
export enum GameStatus {
    Pending = 'pending',
    InProgress = 'in_progress',
    Completed = 'completed',
    Aborted = 'aborted',
}

// Mirrors PHP DataObject Domain\Board\Shared\DataObjects\Player
export type Player = {
    id: number;
    name: string;
};

// Mirrors PHP DataObject Domain\Board\GameSession\DataObjects\Game
export type Game = {
    id: number;
    joinKey: string;
    gameMasterId: number;
    status: GameStatus;
    maxHeroes: number;
    elements: Element[];
    // Tiles come from GameBoard DataObjects; our TS Board type models Tile[][] already
    tiles: Tile[][];
    heroes: (Hero | Zargon)[];
    currentHeroId: number;
    players: Player[];
};
