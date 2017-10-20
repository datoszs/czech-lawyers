import {combineReducers} from 'redux-immutable';
import {Map, List} from 'immutable';
import {SET_LEADERBOARD} from './actions';

const getId = (advocate) => advocate.id_advocate;
const compare = (advocateA, advocateB) => advocateA.sorting_name.localeCompare(advocateB.sorting_name);
const getIdList = (advocates) => List(advocates.sort(compare).map(getId));
const getCourtMap = (courtMap) => new Map(Object.entries(courtMap).map(([court, advocates]) => [parseInt(court, 10), getIdList(advocates)]));
const courtMapReducer = (property) => (state = Map(), action) => (action.type === SET_LEADERBOARD ? getCourtMap(action[property]) : state);

const getAdvocateLists = (courtMap) =>
    Object.values(courtMap).map((advocates) => advocates.map((advocate) => [advocate.id_advocate, advocate.fullname]));
const getMap = (...arrays) => Map([].concat(...arrays));
const nameReducer = (state = Map(), action) =>
    (action.type === SET_LEADERBOARD ? getMap(...getAdvocateLists(action.top), ...getAdvocateLists(action.bottom)) : state);

export default combineReducers({
    top: courtMapReducer('top'),
    bottom: courtMapReducer('bottom'),
    names: nameReducer,
});
