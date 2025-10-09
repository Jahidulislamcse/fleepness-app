<?php

namespace App\Support\Livekit\Contracts;

use Livekit\Room;
use Livekit\ParticipantInfo;
use Livekit\SendDataResponse;
use Livekit\ListRoomsResponse;
use Livekit\DeleteRoomResponse;
use Livekit\MuteRoomTrackResponse;
use Livekit\ParticipantPermission;
use Livekit\MoveParticipantResponse;
use Livekit\ListParticipantsResponse;
use Livekit\RemoveParticipantResponse;
use Livekit\ForwardParticipantResponse;
use Agence104\LiveKit\RoomCreateOptions;
use Livekit\UpdateSubscriptionsResponse;

/**
 * Defines the contract for a LiveKit Room Service client.
 */
interface RoomServiceClient
{
    /**
     * Creates a new room.
     */
    public function createRoom(RoomCreateOptions $createOptions): Room;

    /**
     * List active rooms.
     */
    public function listRooms(array $roomNames = []): ListRoomsResponse;

    /**
     * Delete a room.
     */
    public function deleteRoom(string $roomName): DeleteRoomResponse;

    /**
     * Update the metadata of a room.
     */
    public function updateRoomMetadata(string $roomName, string $metadata): Room;

    /**
     * List the participants in a room.
     */
    public function listParticipants(string $roomName): ListParticipantsResponse;

    /**
     * Get participant info including their published tracks.
     */
    public function getParticipant(string $roomName, string $identity): ParticipantInfo;

    /**
     * Removes a participant in the room.
     */
    public function removeParticipant(string $roomName, string $identity): RemoveParticipantResponse;

    /**
     * Forward a participant's track(s) to another room.
     */
    public function forwardParticipant(string $roomName, string $identity, string $destinationRoom): ForwardParticipantResponse;

    /**
     * Move a connected participant to a different room.
     */
    public function moveParticipant(string $roomName, string $identity, string $destinationRoom): MoveParticipantResponse;

    /**
     * Mutes a track that the participant has published.
     */
    public function mutePublishedTrack(string $roomName, string $identity, string $trackSid, bool $muted): MuteRoomTrackResponse;

    /**
     * Updates a participant's metadata, permissions, name or attributes.
     */
    public function updateParticipant(
        string $roomName,
        string $identity,
        ?string $metadata = null,
        ?ParticipantPermission $permission = null,
        ?string $name = null,
        ?array $attributes = null
    ): ParticipantInfo;

    /**
     * Updates a participant's subscription to tracks.
     *
     * @param  string[]  $trackSids
     */
    public function updateSubscriptions(string $roomName, string $identity, array $trackSids, bool $subscribe): UpdateSubscriptionsResponse;

    /**
     * Sends data message to participants in the room.
     *
     * @param  string[]  $destinationIdentities
     */
    public function sendData(string $roomName, string $data, int $kind, array $destinationIdentities = [], ?string $topic = null): SendDataResponse;
}
