const result = {
    POSITIVE: 'positive',
    NEGATIVE: 'negative',
    NEUTRAL: 'neutral',
    ANNULLED: 'annulled',
};

export const classNameResult = {
    positive: result.POSITIVE,
    negative: result.NEGATIVE,
    neutral: result.NEUTRAL,
};

export const checkResult = (value) => (Object.values(result).includes(value) ? value : null);

export const resultMsg = {
    [result.POSITIVE]: 'result.positive',
    [result.NEGATIVE]: 'result.negative',
    [result.NEUTRAL]: 'result.neutral',
    [result.ANNULLED]: 'result.annulled',
};

export default result;
