import { moveGameHero } from '@/lib/board/game';
import { Board } from '@/types/board';
import { Game, GameStatus, Player } from '@/types/game';
import type { Hero, Zargon } from '@/types/hero';
import { defineStore } from 'pinia';

export const useGameStore = defineStore('game', {
    state: (): Game => ({
        id: 0,
        joinKey: '',
        gameMasterId: 0,
        status: GameStatus.InProgress,
        maxHeroes: 0,
        elements: [],
        tiles: [],
        heroes: [],
        currentHeroId: 0,
        players: [] as Player[],
    }),
    getters: {
        currentHero(state): Hero | Zargon | null {
            return state.heroes.find((h) => h.id === state.currentHeroId) ?? null;
        },
        ownerOfCurrentHero(): Player | null {
            return (this as any).ownerOfHero((this as any).currentHeroId);
        },
    },
    actions: {
        hydrateFromGame(game: Game): void {
            this.id = game.id;
            this.joinKey = game.joinKey;
            this.status = game.status;
            this.gameMasterId = game.gameMasterId;
            this.maxHeroes = game.maxHeroes;
            this.elements = game.elements;
            this.tiles = game.tiles;
            this.heroes = game.heroes;
            this.currentHeroId = game.currentHeroId;
            this.players = game.players;
        },
        moveHero(id: number, toX: number, toY: number, board: Board): boolean {
            return moveGameHero(id, toX, toY, this, board);
        },
        setHeroes(heroes: (Zargon | Hero)[]): void {
            this.heroes = [...heroes];
        },
        setCurrentHero(id: number): void {
            this.currentHeroId = id;
        },
        ownerOfHero(heroId: number): Player | null {
            const hero = this.heroes.find((h) => h.id === heroId);
            if (!hero || hero.playerId == null) {
                return null;
            }
            return this.players.find((p) => p.id === hero.playerId) ?? null;
        },
    },
});
