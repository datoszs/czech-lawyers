import {doGet} from './fetch';

export default {
    getStatistics: () => doGet('/api/court-statistics'),
};
