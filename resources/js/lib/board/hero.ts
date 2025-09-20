import { HeroArchetype, type Hero, type NewHero } from '@/types/hero';

/**
 * Deep clone a hero-like object. Keeps it simple and predictable.
 */
export function cloneHero<T extends object>(hero: T): T {
    return JSON.parse(JSON.stringify(hero)) as T;
}

/**
 * Returns the list of archetypes based on provided heroes collection
 * while preserving the ordering from the backend payload. Falls back
 * to the enum ordering when none are provided.
 */
export function heroArchetypes(heroes?: Array<NewHero | Hero>): HeroArchetype[] {
    const typesFromProps = heroes?.map((h) => h.type) ?? [];
    const uniq = Array.from(new Set(typesFromProps));
    return uniq.length ? (uniq as HeroArchetype[]) : (Object.values(HeroArchetype) as HeroArchetype[]);
}

/**
 * Returns default hero data for the provided archetype. When a matching
 * hero is present in `heroes`, its data is used as a template; otherwise
 * a sane zeroed default is returned.
 */
export function defaultsFor(type: HeroArchetype, heroes?: Array<NewHero | Hero>): NewHero {
    const found = heroes?.find((h) => h.type === type) as (NewHero | Hero | undefined);
    if (found) {
        return cloneHero(found as NewHero);
    }
    return {
        name: '',
        type,
        stats: { bodyPoints: 0, mindPoints: 0, attackDice: 0, defenseDice: 0, currentBodyPoints: 0 },
        inventory: [],
        equipment: [],
    } satisfies NewHero;
}
