import React from 'react';
import PropTypes from 'prop-types';
import {Well} from 'react-bootstrap';
import styles from './SocietyOverview.less';

const SocietyOverview = ({name, street, city, ic}) => (
    <div className={styles.main}>
        <Well>
            <dl>
                <dt>{name}</dt>
                <dd>{ic}</dd>
                <dd>{street}</dd>
                <dd>{city}</dd>
                <dd><a href="mailto:info@ospravedlnosti.cz">info@ospravedlnosti.cz</a></dd>
            </dl>
        </Well>
    </div>
);

SocietyOverview.propTypes = {
    name: PropTypes.string.isRequired,
    ic: PropTypes.string.isRequired,
    street: PropTypes.string.isRequired,
    city: PropTypes.string.isRequired,
};

export default SocietyOverview;
