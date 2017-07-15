import yml from './cs.yml';
import about from './about.cs.md';

/* eslint-disable quote-props */
export default Object.assign({}, yml, {
    'about.text': about,
});
