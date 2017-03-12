/**
 * advocate status
 */
const status = {
    ACTIVE: 'active',
    SUSPENDED: 'suspended',
    REMOVED: 'removed',
};

export const checkStatus = (value) => (Object.values(status).includes(value) ? value : status.ACTIVE);

export const statusMsg = {
    [status.ACTIVE]: 'status.active',
    [status.SUSPENDED]: 'status.suspended',
    [status.REMOVED]: 'status.removed',
};

export default status;
