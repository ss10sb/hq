import { badgeColorForType, Colors } from '@/lib/game/colors';
import Konva from 'konva';

export type SearchBadgeType = 'treasure' | 'trap' | 'secret';
export type SearchBadgeScope = 'tile' | 'corridor' | 'room';

export interface SearchBadge {
    id: string;
    x: number; // px within stage
    y: number; // px within stage
    type: SearchBadgeType;
}

export function badgeLetterForType(type: SearchBadgeType): string {
    switch (type) {
        case 'treasure':
            return 'T';
        case 'trap':
            return '!';
        case 'secret':
            return 'D';
        default:
            return '?';
    }
}

export function makeBadge(x: number, y: number, type: SearchBadgeType): SearchBadge {
    return {
        id: `sb:${Date.now()}:${Math.random().toString(36).slice(2, 8)}`,
        x,
        y,
        type,
    };
}

export function badgeGroupConfig(badge: SearchBadge): any {
    return {
        x: badge.x,
        y: badge.y,
        draggable: false,
        listening: false,
    };
}

export function badgeCircleConfig(badge: SearchBadge): any {
    return {
        x: 0,
        y: 0,
        radius: 8,
        fill: badgeColorForType(badge.type),
        stroke: '#111827',
        strokeWidth: 1,
        opacity: 1,
        listening: false,
    };
}

export function badgeTextConfig(badge: SearchBadge): any {
    const tempText = new Konva.Text({
        x: 0,
        y: 0,
        text: badgeLetterForType(badge.type),
        fontSize: 10,
        fontStyle: 'bold',
        fill: Colors.White,
        listening: false,
        align: 'center',
        verticalAlign: 'middle',
    });
    const offsetX = tempText.width() / 2;
    const offsetY = tempText.height() / 2;
    return {
        x: 0,
        y: 0,
        text: badgeLetterForType(badge.type),
        fontSize: 10,
        fontStyle: 'bold',
        fill: Colors.White,
        listening: false,
        align: 'center',
        verticalAlign: 'middle',
        offsetX: offsetX,
        offsetY: offsetY,
    };
}
