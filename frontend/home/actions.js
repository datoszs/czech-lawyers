import {NAME} from './constants';

export const SET_LEADERBOARD = `${NAME}/SET_LEADERBOARD`;

export const setLeaderBoard = (top = [], bottom = []) => ({
    type: SET_LEADERBOARD,
    top,
    bottom,
});
