export {default as transition} from './transition';
export {default as TestingStore} from './TestingStore';

export const wrapEventStop = (handler) => (event) => {
    event.stopPropagation();
    event.preventDefault();
    handler(event);
};
