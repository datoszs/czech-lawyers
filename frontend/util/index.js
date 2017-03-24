export {default as transition} from './transition';
export {default as TestingStore} from './TestingStore';

export const wrapEventStop = (handler) => (event) => {
    event.stopPropagation();
    event.preventDefault();
    handler(event);
};

export const getCurrentYear = () => new Date().getFullYear();

export const sequence = (length) => Array.from({length}, (value, index) => index);
