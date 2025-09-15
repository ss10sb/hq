<template>
    <div class="game-board-container">
        <!-- Mode Selection -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">HeroQuest Game Board</h1>

            <div class="flex gap-4 mb-6">
                <button
                    @click="switchUser(demoGameMaster)"
                    :class="[
            'px-6 py-3 rounded-lg font-medium transition-colors',
            isGameMaster
              ? 'bg-purple-600 text-white shadow-lg'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
                >
                    Game Master View
                </button>
                <button
                    @click="switchUser(demoPlayer)"
                    :class="[
            'px-6 py-3 rounded-lg font-medium transition-colors',
            isPlayer
              ? 'bg-blue-600 text-white shadow-lg'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
                >
                    Player View
                </button>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg mb-4">
                <h3 class="font-semibold text-blue-900 mb-2">
                    {{ currentUser.name }} ({{ isGameMaster ? 'Game Master' : 'Player' }})
                </h3>
                <p class="text-blue-800 text-sm">
                    {{ isGameMaster
                    ? 'Click tiles to place walls or floors. Adjust board dimensions using the controls above.'
                    : 'View the game board. Interactive floor tiles are clickable.' }}
                </p>
                <div class="mt-2 text-xs text-blue-600">
                    User ID: {{ currentUser.id }} |
                    <span v-if="isGameMaster">Session: {{ (currentUser as GameMaster).sessionId }}</span>
                    <span v-else-if="isPlayer && (currentUser as Player).characterId">
            Character: {{ (currentUser as Player).characterId }}
          </span>
                </div>
            </div>
        </div>

        <!-- Controls for editing mode -->
        <div v-if="canEditBoard" class="mb-4 p-4 bg-gray-100 rounded-lg">
            <div class="flex items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold">Board Editor</h3>
                <div class="text-sm text-gray-600">
                    Permissions:
                    <span v-if="isGameMaster">
            Edit Board: {{ (currentUser as GameMaster).permissions.editBoard ? '✓' : '✗' }},
            Manage Characters: {{ (currentUser as GameMaster).permissions.manageCharacters ? '✓' : '✗' }},
            Control NPCs: {{ (currentUser as GameMaster).permissions.controlNPCs ? '✓' : '✗' }}
          </span>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="selectedTileType = 'wall'"
                        :class="[
              'px-4 py-2 rounded',
              selectedTileType === 'wall' 
                ? 'bg-gray-600 text-white' 
                : 'bg-gray-200 text-gray-700'
            ]"
                    >
                        Wall
                    </button>
                    <button
                        @click="selectedTileType = 'floor'"
                        :class="[
              'px-4 py-2 rounded',
              selectedTileType === 'floor' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 text-gray-700'
            ]"
                    >
                        Floor
                    </button>
                </div>
            </div>

            <div class="flex gap-4 items-center">
                <div class="flex items-center gap-2">
                    <label for="board-width" class="text-sm font-medium">Width:</label>
                    <input
                        id="board-width"
                        v-model.number="boardDimensions.width"
                        type="number"
                        class="w-20 px-2 py-1 border rounded"
                        min="1"
                        max="50"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <label for="board-height" class="text-sm font-medium">Height:</label>
                    <input
                        id="board-height"
                        v-model.number="boardDimensions.height"
                        type="number"
                        class="w-20 px-2 py-1 border rounded"
                        min="1"
                        max="50"
                    />
                </div>
            </div>
        </div>

        <!-- Board Canvas -->
        <div class="board-canvas-container border-2 border-gray-300 rounded-lg overflow-auto">
            <v-stage
                ref="stageRef"
                :config="stageConfig"
                class="game-board-stage"
            >
                <v-layer>
                    <v-group>
                        <v-rect
                            v-for="tile in tiles"
                            :key="tile.id"
                            :config="{
                x: tile.position.x * tileSize,
                y: tile.position.y * tileSize,
                width: tileSize,
                height: tileSize,
                fill: getTileColor(tile),
                stroke: getTileStroke(tile),
                strokeWidth: 1,
                listening: isEditable || tile.isInteractive,
                cursor: isEditable ? 'pointer' : tile.isInteractive ? 'pointer' : 'default'
              }"
                            @click="handleTileClick(tile)"
                            @tap="handleTileClick(tile)"
                        />
                    </v-group>
                </v-layer>
            </v-stage>
        </div>

        <!-- Board Info -->
        <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm">
            <div class="flex gap-6">
                <span>Board Size: {{ boardDimensions.width }} × {{ boardDimensions.height }}</span>
                <span>Total Tiles: {{ tiles.length }}</span>
                <span>Floor Tiles: {{ floorTileCount }}</span>
                <span>Wall Tiles: {{ wallTileCount }}</span>
                <span>Current User: {{ currentUser.name }} ({{ currentUser.type }})</span>
            </div>
        </div>

        <!-- Future Features Info -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-semibold mb-2">Future Features Planned:</h3>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• Multiple tile layers (traps, monsters, characters, fixtures)</li>
                <li>• Real-time multiplayer synchronization via WebSockets</li>
                <li>• Save/load board configurations</li>
                <li>• Drag & drop character placement</li>
                <li>• Tile-based line of sight calculations</li>
                <li>• Interactive objects and furniture placement</li>
                <li>• Role-based permissions and user management</li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'

// User role interfaces
interface BaseUser {
    id: string
    name: string
    type: 'player' | 'gamemaster'
}

interface Player extends BaseUser {
    type: 'player'
    characterId?: string
    position?: TilePosition
    canEdit: false
}

interface GameMaster extends BaseUser {
    type: 'gamemaster'
    sessionId: string
    canEdit: true
    permissions: {
        editBoard: boolean
        manageCharacters: boolean
        controlNPCs: boolean
    }
}

type User = Player | GameMaster

// Board types for future extensibility
interface BoardDimensions {
    width: number
    height: number
}

interface TilePosition {
    x: number
    y: number
}

interface BaseTile {
    id: string
    position: TilePosition
    type: 'wall' | 'floor' // Base types, can be extended
    isInteractive: boolean
}

// Props with defaults
interface Props {
    initialDimensions?: BoardDimensions
    tileSize?: number
    currentUser?: User
    onTileClick?: (tile: BaseTile) => void
}

const props = withDefaults(defineProps<Props>(), {
    initialDimensions: () => ({ width: 22, height: 28 }),
    tileSize: 25,
    currentUser: () => ({
        id: 'demo-gm',
        name: 'Demo Game Master',
        type: 'gamemaster',
        sessionId: 'demo-session',
        canEdit: true,
        permissions: {
            editBoard: true,
            manageCharacters: true,
            controlNPCs: true
        }
    } as GameMaster)
})

// Emits
const emit = defineEmits<{
    tileClick: [tile: BaseTile]
    userChanged: [user: User]
}>()

// Demo users for switching between roles
const demoGameMaster: GameMaster = {
    id: 'demo-gm',
    name: 'Demo Game Master',
    type: 'gamemaster',
    sessionId: 'demo-session',
    canEdit: true,
    permissions: {
        editBoard: true,
        manageCharacters: true,
        controlNPCs: true
    }
}

const demoPlayer: Player = {
    id: 'demo-player',
    name: 'Demo Player',
    type: 'player',
    characterId: 'hero-001',
    canEdit: false
}

// Reactive state
const currentUser = ref<User>(props.currentUser)
const boardDimensions = ref<BoardDimensions>({ ...props.initialDimensions })
const tiles = ref<BaseTile[]>([])
const selectedTileType = ref<'wall' | 'floor'>('floor')
const stageRef = ref()

// Computed properties
const isGameMaster = computed(() => currentUser.value.type === 'gamemaster')

const isPlayer = computed(() => currentUser.value.type === 'player')

const isEditable = computed(() => currentUser.value.canEdit && currentUser.value.type === 'gamemaster')

const canEditBoard = computed(() => {
    return currentUser.value.type === 'gamemaster' &&
        (currentUser.value as GameMaster).permissions.editBoard
})

const stageConfig = computed(() => ({
    width: boardDimensions.value.width * props.tileSize,
    height: boardDimensions.value.height * props.tileSize
}))

const floorTileCount = computed(() =>
    tiles.value.filter(t => t.type === 'floor').length
)

const wallTileCount = computed(() =>
    tiles.value.filter(t => t.type === 'wall').length
)

// Methods
const initializeBoard = () => {
    const newTiles: BaseTile[] = []

    for (let y = 0; y < boardDimensions.value.height; y++) {
        for (let x = 0; x < boardDimensions.value.width; x++) {
            newTiles.push({
                id: `tile-${x}-${y}`,
                position: { x, y },
                type: 'wall',
                isInteractive: false
            })
        }
    }

    tiles.value = newTiles
}

const handleTileClick = (clickedTile: BaseTile) => {
    if (isEditable.value && canEditBoard.value) {
        // Game Master mode - edit tiles
        const tileIndex = tiles.value.findIndex(tile => tile.id === clickedTile.id)
        if (tileIndex !== -1) {
            const newType = selectedTileType.value
            tiles.value[tileIndex] = {
                ...tiles.value[tileIndex],
                type: newType,
                isInteractive: newType === 'floor'
            }
        }
    } else if (isPlayer.value && clickedTile.isInteractive) {
        // Player mode - only interact with floor tiles
        console.log(`Player ${currentUser.value.name} clicked on interactive tile:`, clickedTile)
        // Future: Handle player movement, actions, etc.
    }

    // Emit event for parent component handling
    emit('tileClick', clickedTile)

    // Call optional prop callback
    if (props.onTileClick) {
        props.onTileClick(clickedTile)
    }
}

const getTileColor = (tile: BaseTile): string => {
    switch (tile.type) {
        case 'wall':
            return '#4a5568' // Dark gray for walls
        case 'floor':
            return '#f7fafc' // Light gray for floor
        default:
            return '#e2e8f0'
    }
}

const getTileStroke = (tile: BaseTile): string => {
    return tile.type === 'floor' ? '#cbd5e0' : '#2d3748'
}

// Method to switch user (for demo purposes)
const switchUser = (user: User) => {
    currentUser.value = user
    emit('userChanged', user)
}

// Watchers
watch(boardDimensions, () => {
    initializeBoard()
}, { deep: true })

watch(currentUser, (newUser) => {
    console.log(`User switched to: ${newUser.name} (${newUser.type})`)
}, { deep: true })

// Lifecycle
onMounted(() => {
    initializeBoard()
})

// Expose methods for potential parent component use
defineExpose({
    getTiles: () => tiles.value,
    setTiles: (newTiles: BaseTile[]) => { tiles.value = newTiles },
    resizeBoard: (newDimensions: BoardDimensions) => {
        boardDimensions.value = newDimensions
    },
    getCurrentUser: () => currentUser.value,
    setCurrentUser: (user: User) => { currentUser.value = user },
    switchUser,
    // Type guards for external use
    isGameMaster: () => isGameMaster.value,
    isPlayer: () => isPlayer.value,
    canEdit: () => canEditBoard.value
})
</script>

<style scoped>
.game-board-container {
    @apply max-w-6xl mx-auto p-6;
}

.board-canvas-container {
    max-width: 100%;
    max-height: 70vh;
}

.game-board-stage {
    @apply bg-white;
}
</style>