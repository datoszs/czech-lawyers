import {NAME} from './constants';

export const SET_STATISTICS = `${NAME}/SET_STATISTICS`;
export const setStatistics = (statistics) => ({
    type: SET_STATISTICS,
    statistics,
});
