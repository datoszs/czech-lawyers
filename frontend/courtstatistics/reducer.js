import {Map} from 'immutable';
import {Statistics} from '../model';
import {SET_STATISTICS} from './actions';

export default (state = Map(), action) => {
    if (action.type === SET_STATISTICS) {
        return Map(Object.entries(action.statistics).map(
            ([court, statistics]) => [parseInt(court, 10), new Statistics(statistics)]),
        );
    } else {
        return state;
    }
};
