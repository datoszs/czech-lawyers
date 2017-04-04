import React, {PropTypes} from 'react';
import {Panel} from 'react-bootstrap';

const SocietyOverview = ({name, street, city, ic}) => (
    <div className="society-overview">
        <Panel>
            <dl>
                <dt>{name}</dt>
                <dd>{ic}</dd>
                <dd>{street}</dd>
                <dd>{city}</dd>
                <dd><a href="mailto:info@cestiadvokati.cz">info@cestiadvokati.cz</a></dd>
            </dl>
        </Panel>
    </div>
);

SocietyOverview.propTypes = {
    name: PropTypes.string.isRequired,
    ic: PropTypes.string.isRequired,
    street: PropTypes.string.isRequired,
    city: PropTypes.string.isRequired,
};

export default SocietyOverview;
