class CreateHrmEnsembleEngagementStatuses < ActiveRecord::Migration
  def change
    create_table :hrm_ensemble_engagement_statuses do |t|
      t.string :name, :limit => 50

      # t.timestamps
    end
  end
end
