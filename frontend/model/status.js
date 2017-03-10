/**
 * advocate status
 */
const status = {
    ACTIVE: 'active',
    SUSPENDED: 'suspended',
    REMOVED: 'removed',
};

export const checkStatus = (value) => (Object.values(status).includes(value) ? value : status.ACTIVE);

export default status;
