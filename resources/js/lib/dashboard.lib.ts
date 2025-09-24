import { GameStatus } from '@/types/dashboard';

export function statusLabel(status: GameStatus): string {
    switch (status) {
        case GameStatus.Pending:
            return 'Pending';
        case GameStatus.InProgress:
            return 'In Progress';
        case GameStatus.Completed:
            return 'Completed';
        case GameStatus.Aborted:
            return 'Aborted';
        default:
            return 'Unknown';
    }
}
