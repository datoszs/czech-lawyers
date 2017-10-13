import {combineReducers} from 'redux-immutable';
import {Map, List} from 'immutable';
import {SET_LEADERBOARD} from './actions';

const getId = (advocate) => advocate.id_advocate;
const compare = (advocateA, advocateB) => advocateA.sorting_name.localeCompare(advocateB.sorting_name);
const getIdList = (advocates) => List(advocates.sort(compare).map(getId));
const topReducer = (state = List(), action) => (action.type === SET_LEADERBOARD ? getIdList(action.top) : state);
const bottomReducer = (state = List(), action) => (action.type === SET_LEADERBOARD ? getIdList(action.bottom) : state);

const getMap = (...arrays) => Map([].concat(...arrays.map((array) => array.map((advocate) => [advocate.id_advocate, advocate.fullname]))));
const nameReducer = (state = Map(), action) => (action.type === SET_LEADERBOARD ? getMap(action.top, action.bottom) : state);

export default combineReducers({
    top: topReducer,
    bottom: bottomReducer,
    names: nameReducer,
});
