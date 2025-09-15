import { MonsterType } from '@/types/board';
import { Stats } from '@/types/gameplay';

export function statsForMonsterType(monsterType: MonsterType): Stats {
    const defaultStats = {
        bodyPoints: 1,
        mindPoints: 1,
        attackDice: 2,
        defenseDice: 2,
        currentBodyPoints: 1,
    };
    if (monsterType === MonsterType.Custom) {
        return defaultStats;
    }
    switch (monsterType) {
        case MonsterType.Goblin:
            return { ...defaultStats, bodyPoints: 1, mindPoints: 1 };
        case MonsterType.Orc:
            return { ...defaultStats, bodyPoints: 1, mindPoints: 2, attackDice: 3 };
        case MonsterType.Skeleton:
            return { ...defaultStats, bodyPoints: 1, mindPoints: 2 };
        default:
            return defaultStats;
    }
}
