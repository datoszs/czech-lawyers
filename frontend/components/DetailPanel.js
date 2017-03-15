import React, {PropTypes, Component} from 'react';
import {Panel} from 'react-bootstrap';
import classnames from 'classnames';
import PanelBody from './PanelBody';

class DetailPanel extends Component {
    constructor(props) {
        super(props);
        this.state = {
            active: false,
        };
        this.handleEnter = this.setActive.bind(this, true);
        this.handleExit = this.setActive.bind(this, false);
    }

    setActive(active) {
        if (active !== this.state.active) {
            this.setState({active});
        }
    }

    render() {
        const titleClass = classnames({
            title: true,
            active: this.state.active,
        });

        return (
            <Panel
                bsStyle={this.state.active ? 'primary' : 'default'}
                className="detail-panel"
                footer={this.props.footer}
                onMouseMove={this.handleEnter}
                onMouseLeave={this.handleExit}
                onClick={this.props.onClick}
            >
                <PanelBody>
                    <h2 className={titleClass}>{this.props.title}</h2>
                    {this.props.children}
                </PanelBody>
            </Panel>
        );
    }
}

DetailPanel.propTypes = {
    children: PropTypes.node.isRequired,
    footer: PropTypes.node,
    title: PropTypes.node.isRequired,
    onClick: PropTypes.func,
};

DetailPanel.defaultProps = {
    footer: null,
    onClick: () => {}, // do nothing
};

export default DetailPanel;
