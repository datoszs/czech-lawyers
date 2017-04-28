import searchModule from '../search';
import {advocateAPI} from '../serverAPI';
import {Advocate, mapDtoToAdvocate} from '../model';
import {NAME} from './constants';

export const search = searchModule({
    reducerPath: [NAME],
    actionPrefix: `${NAME}/advocates`,
    api: advocateAPI.search,
    Model: Advocate,
    transformation: mapDtoToAdvocate,
});
