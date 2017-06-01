export const NAME = 'disputation-verification';

export const result = {
    SUCCESS: 'success',
    INVALID_INPUT: 'invalid_input',
    EXPIRED: 'expired',
    NO_REQUEST: 'no_request',
    VALIDATED: 'already_validated',
    INCONSISTENT_FINAL: 'inconsistent_already_final',
    INCONSISTENT_CHANGED: 'inconsistent_changed_meanwhile',
    FAIL: 'fail',
};

export const resultStyle = {
    [result.SUCCESS]: 'success',
    [result.VALIDATED]: 'success',
};

export const resultMsg = {
    [result.SUCCESS]: 'case.dispute.verify.success',
    [result.VALIDATED]: 'case.dispute.verify.success',
    [result.NO_REQUEST]: 'case.dispute.verify.no.request',
    [result.INCONSISTENT_CHANGED]: 'case.dispute.verify.inconsistent',
    [result.INCONSISTENT_FINAL]: 'case.dispute.verify.inconsistent',
};
