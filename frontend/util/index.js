export {default as TestingStore} from './TestingStore';
export {default as formatRoute} from './formatRoute';
export {default as LifecycleListener} from './LifecycleListener';
export {default as If} from './If';

export const wrapEventStop = (handler) => (event) => {
    event.stopPropagation();
    event.preventDefault();
    handler(event);
};

export const wrapLinkMouseEvent = (onClick) => (event) => {
    if (event.button === 0 && !event.ctrlKey && !event.altKey && !event.metaKey && !event.shiftKey) {
        event.preventDefault();
        event.stopPropagation();
        onClick(event);
    }
};

export const getCurrentYear = () => new Date().getFullYear();

export const sequence = (length) => Array.from({length}, (value, index) => index);

export const dateFormat = 'Y-MM-DD\\THH:mm:ssZ';

export const toObject = (result, [key, value]) => Object.assign({[key]: value}, result);
