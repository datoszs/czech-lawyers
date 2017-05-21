export {default as transition} from './transition';
export {default as TestingStore} from './TestingStore';
export {default as formatRoute} from './formatRoute';
export {default as LifecycleListener} from './LifecycleListener';

export const wrapEventStop = (handler) => (event) => {
    event.stopPropagation();
    event.preventDefault();
    handler(event);
};

export const getCurrentYear = () => new Date().getFullYear();

export const sequence = (length) => Array.from({length}, (value, index) => index);

export const dateFormat = 'Y-MM-DD\\THH:mm:ssZ';
