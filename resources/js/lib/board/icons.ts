import { Bomb, DoorClosed, DoorOpen, Flag, Gem, Key, Play, Skull, UserRound } from 'lucide';

// Lucide SVG rendering for Konva
export const ICON_SIZE = 24;

type IconChild = [string, Record<string, any>];
type IconNode = IconChild[]; // Lucide icon nodes are arrays of [tag, attrs]

const iconMap: Record<string, IconNode | undefined> = {
    monster: Skull as unknown as IconNode,
    hero: UserRound as unknown as IconNode,
    door: DoorClosed as unknown as IconNode,
    door_open: DoorOpen as unknown as IconNode,
    secret_door: Key as unknown as IconNode,
    trap: Bomb as unknown as IconNode,
    treasure: Gem as unknown as IconNode,
    player_start: Play as unknown as IconNode,
    player_exit: Flag as unknown as IconNode,
};

const iconColor: Record<string, string> = {
    monster: 'oklch(50.5% 0.213 27.518)', // red-700
    door: 'oklch(27.9% 0.077 45.635)', // amber-950
    door_open: 'oklch(27.9% 0.077 45.635)', // amber-950
    secret_door: 'oklch(28.3% 0.141 291.089)', // violet-950
    trap: '#f59e0b', // amber-500
    treasure: '#10b981', // emerald-500
    player_start: '#22c55e', // green-500
    player_exit: '#3b82f6', // blue-500
};

function iconNodeToSvg(children: IconNode, color: string, size = ICON_SIZE): string {
    const baseAttrs: Record<string, string> = {
        xmlns: 'http://www.w3.org/2000/svg',
        width: String(size),
        height: String(size),
        viewBox: '0 0 24 24',
        fill: 'none',
        stroke: color,
        'stroke-width': '2',
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
    };
    const attrsToString = (a: Record<string, any>) =>
        Object.entries(a)
            .map(([k, v]) => `${k}="${String(v)}"`)
            .join(' ');

    const childStr = (children || []).map(([cTag, cAttrs]) => `<${cTag} ${attrsToString(cAttrs)}></${cTag}>`).join('');

    return `<svg ${attrsToString(baseAttrs)}>${childStr}</svg>`;
}

const imageCache = new Map<string, HTMLImageElement>();

export function prepareIcon(type: string, colorOverride?: string): void {
    const key = colorOverride ? `${type}:${colorOverride}` : `${type}`;
    if (imageCache.has(key)) {
        return;
    }
    const node = iconMap[type];
    if (!node) {
        return;
    }
    const color = colorOverride ?? iconColor[type] ?? '#38bdf8';
    const svg = iconNodeToSvg(node, color, ICON_SIZE);
    const img = new Image();
    img.onerror = () => {
        imageCache.delete(key);
    };
    img.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
    imageCache.set(key, img);
}

export function iconFor(type: string, colorOverride?: string): HTMLImageElement | null {
    const key = colorOverride ? `${type}:${colorOverride}` : `${type}`;
    let img = imageCache.get(key);
    if (!img) {
        // Ensure the icon is prepared if it hasn't been requested yet
        prepareIcon(type, colorOverride);
        img = imageCache.get(key) ?? (null as any);
    }
    // Return the Image element immediately. Konva will re-draw the node when the
    // image finishes loading because it listens to image.onload internally.
    // Returning null would prevent the node from existing and no redraw would occur.
    return (img as any) ?? null;
}

export function preloadAllIcons(): void {
    Object.keys(iconMap).forEach((k) => prepareIcon(k));
}
