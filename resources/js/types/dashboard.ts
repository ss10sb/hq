export type Board = {
    id: number;
    name: string;
    group: string;
    order: number;
};

export type Game = {
    id: number;
    joinKey: string;
    gameMasterId: number;
    status: GameStatus;
    board: Board;
};

export enum GameStatus {
    Pending = 'pending',
    InProgress = 'in_progress',
    Completed = 'completed',
    Aborted = 'aborted',
}
