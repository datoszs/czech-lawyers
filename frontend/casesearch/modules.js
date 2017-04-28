import searchModule from '../search';
import {caseAPI} from '../serverAPI';
import {Case, mapDtoToCase} from '../model';
import {NAME} from './constants';

export const search = searchModule({
    reducerPath: [NAME],
    actionPrefix: `${NAME}/cases`,
    api: caseAPI.search,
    Model: Case,
    transformation: mapDtoToCase,
});
