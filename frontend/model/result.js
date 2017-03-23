const result = {
    POSITIVE: 'positive',
    NEGATIVE: 'negative',
    NEUTRAL: 'neutral',
};

export const classNameResult = {
    positive: result.POSITIVE,
    negative: result.NEGATIVE,
    neutral: result.NEUTRAL,
};

export const checkResult = (value) => (Object.values(result).includes(value) ? value : null);

export default result;
