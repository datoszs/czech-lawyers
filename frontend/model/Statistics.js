import {Record} from 'immutable';

/**
 * Advocate statistics
 * @property {number} positive
 * @property {number} negative
 * @property {number} neutral
 */
const Statistics = Record({
    positive: null,
    negative: null,
    neutral: null,
});

export default Statistics;
